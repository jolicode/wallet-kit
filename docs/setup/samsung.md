# Setup wallet-kit with Samsung Wallet

This tutorial walks you through testing the Samsung Wallet integration of `wallet-kit` from scratch, all the way to seeing a card installed on your Galaxy phone.

Expected time: **~30 minutes** once your Samsung Partner account is approved. The portal review itself can take **several business days** — plan ahead.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Step 1 — Samsung Wallet Business Portal onboarding](#step-1--samsung-wallet-business-portal-onboarding)
- [Step 2 — Choose your region](#step-2--choose-your-region)
- [Step 3 — Generate an RSA keypair and upload the public key](#step-3--generate-an-rsa-keypair-and-upload-the-public-key)
- [Step 4 — Grab your Partner ID and (optional) Service ID](#step-4--grab-your-partner-id-and-optional-service-id)
- [Step 5 — Set up the test project](#step-5--set-up-the-test-project)
- [Step 6 — Create a card via the API](#step-6--create-a-card-via-the-api)
- [Step 7 — Open the Add-to-Wallet URL on your Galaxy](#step-7--open-the-add-to-wallet-url-on-your-galaxy)
- [Step 8 — Verify in Samsung Wallet](#step-8--verify-in-samsung-wallet)
- [Step 9 — Test a push update](#step-9--test-a-push-update)
- [Debugging](#debugging)
- [Iterating quickly](#iterating-quickly)
- [Recap](#recap)

---

## Prerequisites

- A **Galaxy** phone with the **Samsung Wallet** app installed and a Samsung account signed in (the app is Galaxy-only — it does not exist on other Android OEMs or iOS)
- PHP 8.3+ and Composer installed locally
- The `ext-openssl` PHP extension enabled
- A Samsung account eligible to register as a Samsung Wallet partner (business/organization info may be required)

---

## Step 1 — Samsung Wallet Business Portal onboarding

Unlike Google (instant Demo mode) and Apple (pay-and-start), Samsung gates its Wallet API behind a **manual partner review**. Expect a few business days of back-and-forth.

Go to the Samsung Wallet Business Portal for your region (see [Step 2](#step-2--choose-your-region) to pick the right one), sign in with your Samsung account, and submit the partner application. You will be asked for:

- Company / organization information (legal name, address, contact email)
- A description of the pass use case (coupons, loyalty, ticketing, …)
- A sample pass layout (screenshots or mockups)

Once approved, the portal unlocks the API section where you can register keys and create test cards.

> **Heads-up:** onboarding terminology and the exact portal URL change from time to time and by region. If a link below 404s, search for "Samsung Wallet Business Portal" or "Samsung Wallet Partners" for the current entry point.

---

## Step 2 — Choose your region

Samsung runs three independent regional stacks — **US, EU, KR** — and they are strictly isolated:

| Region | Base URL                                              | Enum case                      |
| ------ | ----------------------------------------------------- | ------------------------------ |
| US     | `https://api-us1.mpay.samsung.com/wallet/v2.1/`       | `SamsungRegionEnum::US`        |
| EU     | `https://api-eu1.mpay.samsung.com/wallet/v2.1/`       | `SamsungRegionEnum::EU` (default) |
| KR     | `https://api-kr.mpay.samsung.com/wallet/v2.1/`        | `SamsungRegionEnum::KR`        |

**The region you register on determines the only API endpoint you can use** — a Partner ID issued by the EU portal cannot be used against the US API, and vice-versa. Pick the region where your users live, and where the Samsung account signed in on your test Galaxy phone is billed. A mismatch between the partner region and the phone's Samsung account region is the most common "my card does not appear in Samsung Wallet" cause.

---

## Step 3 — Generate an RSA keypair and upload the public key

Samsung authenticates API calls with a **JWT signed by your own RSA private key** (RS256). The portal stores your public key and rejects any JWT that does not verify against it.

Generate a 2048-bit keypair locally:

```bash
openssl genrsa -out samsung-private.pem 2048
openssl rsa -in samsung-private.pem -pubout -out samsung-public.pem
```

Keep `samsung-private.pem` somewhere safe (e.g. `~/.wallet-kit-test/samsung-private.pem`, `chmod 600`). You will never share it.

In the portal → section **Keys** (or **Credentials**, depending on region), upload `samsung-public.pem`. The portal may show a short fingerprint or key ID after upload — you do not need to pass it to `wallet-kit`, but note it for your own records.

---

## Step 4 — Grab your Partner ID and (optional) Service ID

Still in the portal:

- **Partner ID** — shown at the top of the dashboard or under **Settings → General**. Usually a short alphanumeric string. This is what `wallet-kit` sends as the `iss` claim of every JWT.
- **Service ID** — optional; some partner accounts get one extra identifier for isolating multiple products under the same partner. If the portal does not show one, leave `SAMSUNG_SERVICE_ID` empty in your `.env.local`.

Keep both values handy.

---

## Step 5 — Set up the test project

Create a test folder:

```bash
mkdir ~/wallet-kit-samsung-test && cd ~/wallet-kit-samsung-test
```

Copy the `composer.json` template from [`examples/samsung/composer.json`](../../examples/samsung/composer.json) into your folder, then install:

```bash
composer install
```

Copy your private key into the folder and lock it down:

```bash
cp ~/path/to/samsung-private.pem ./samsung-private.pem
chmod 600 samsung-private.pem
```

Then copy [`examples/samsung/.env`](../../examples/samsung/.env) to `.env.local` and fill in your values:

```bash
cp .env .env.local
```

> **Where to put your secrets:** `.env` is the committed template with placeholder values — leave it alone. Fill your real values in `.env.local` (gitignored). Symfony Dotenv loads `.env` first and then overlays `.env.local` on top, so `.env.local` always wins.

Required keys:

- `SAMSUNG_PARTNER_ID` — from Step 4
- `SAMSUNG_PRIVATE_KEY_PATH` — absolute path to `samsung-private.pem`
- `SAMSUNG_REGION` — `US`, `EU`, or `KR` (matches Step 2)
- `SAMSUNG_SERVICE_ID` — optional (from Step 4)
- `SAMSUNG_CARD_ID` — needed only by `test-push.php`, populated after Step 6

---

## Step 6 — Create a card via the API

Copy [`examples/samsung/test-api.php`](../../examples/samsung/test-api.php) into your folder. The script reads everything from `.env.local` — no edits needed.

It builds an **Offer (coupon) pass** via `WalletPass::offer(...)` with `->withSamsung(refId: ...)`, then POSTs it to the regional Samsung Wallet API via `SamsungWalletClient::createCard()`. If you want to try another vertical (loyalty, event ticket, gift card, …), swap the `WalletPass::offer(...)` call — the builder reports missing required parameters clearly at runtime.

Run:

```bash
php test-api.php
```

Expected output:

```
→ Creating Samsung card in region EU...
✓ Card created (cardId: 01HX...).

   SAMSUNG_CARD_ID=01HX...
   (copy this into your .env.local to use test-push.php)

=== Add-to-Wallet URL ===
https://a.wallet.samsung.com/wallet/card?cardId=01HX...&partnerId=...
```

---

## Step 7 — Open the Add-to-Wallet URL on your Galaxy

Three options to transfer the URL, same as the Google flow:

**Option A (fastest)** — generate a QR code in the terminal:

```bash
echo "URL_PASTED_HERE" | qrencode -t ANSIUTF8
```

Then scan it from the Galaxy camera.

**Option B** — email the URL to yourself or send via messenger, then tap it on the phone.

**Option C** — save the URL to a local HTML file and open it:

```html
<a href="URL_HERE">Add to Samsung Wallet</a>
```

---

## Step 8 — Verify in Samsung Wallet

You should see the Samsung Wallet "Add card" screen with a preview. Tap **Add**.

Open the Samsung Wallet app — the card is there. 🎉

If it fails, see the [Debugging](#debugging) section below.

---

## Step 9 — Test a push update

To confirm that `pushCardUpdate` works, set `SAMSUNG_CARD_ID` in `.env.local` to the value printed in Step 6, then copy [`examples/samsung/test-push.php`](../../examples/samsung/test-push.php) into your folder and run:

```bash
php test-push.php
```

A `✓ Push update accepted` response means Samsung queued the notification. On the phone, open the card in Samsung Wallet and pull down to refresh — any server-side changes propagate at this point.

Note that Samsung's server-to-device push is a *hint*: to actually change what the user sees, you must also `updateCard()` or `updateCardState()` on the API. `pushCardUpdate()` just nudges the phone to re-fetch.

---

## Debugging

### 401 `Unauthorized` or `Invalid token`

The JWT the client generated does not verify against the public key Samsung has on file. Causes, in order:

- Wrong `SAMSUNG_PARTNER_ID` — it must be the exact value shown in the portal (no whitespace, no quotes).
- The private key at `SAMSUNG_PRIVATE_KEY_PATH` does not match the public key you uploaded. Regenerate both and re-upload.
- File unreadable by PHP (`chmod 600` with the wrong user). Quick check: `openssl rsa -in samsung-private.pem -noout -check`.

### 403 `Forbidden` / `Service not activated`

Partner account is registered but the specific service / API scope is not yet enabled. Contact Samsung via the portal.

### 404 on every endpoint

Region mismatch — you are hitting `api-eu1.*` with a partner registered on `api-us1.*` (or vice-versa). Fix `SAMSUNG_REGION` in `.env.local`.

### 429 `Too Many Requests`

Samsung rate-limits aggressively during sandbox. Back off and retry after the `Retry-After` header. The library exposes the raw body so you can inspect it.

### The card creates fine but does not appear on the phone

Most common cause: the Samsung account signed in on your Galaxy is in a different region from your partner account. A card created on EU cannot be pulled into a Samsung account provisioned in the US store. Either switch the phone's account region or create a test partner on the matching region.

Less common: the `refId` you used has already been bound to a different user on the same partner. Use a fresh `refId` for each test run (the script already uses a timestamp).

### `Unable to read private key from Samsung credentials`

The `.pem` file is corrupted or PHP cannot open it. Validate:

```bash
openssl rsa -in samsung-private.pem -noout -check
# Should print: "RSA key ok"
```

### Deep network debugging

Wrap the HTTP client in `TraceableHttpClient` (same pattern as the Google tutorial):

```php
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\TraceableHttpClient;

$http = new TraceableHttpClient(HttpClient::create());
// ... pass $http to new SamsungWalletClient(...) ...
foreach ($http->getTracedRequests() as $trace) {
    echo $trace['method'] . ' ' . $trace['url'] . ' → ' . $trace['info']['http_code'] . "\n";
}
```

The JWT sent in the `Authorization` header can be decoded on **[jwt.io](https://jwt.io)** (copy the part after `Bearer `) to check the `iss` / `iat` / `exp` claims.

---

## Iterating quickly

- Use a unique `refId` per run (the script already uses a timestamp). Reusing a `refId` that was previously bound to a user will be rejected.
- In the portal, test cards can be deleted in bulk.

To test without hitting Samsung at all, run the `wallet-kit` Samsung unit tests:

```bash
cd /path/to/wallet-kit
vendor/bin/phpunit tests/Api/Samsung/
```

They use `MockHttpClient` — no network — and validate request shapes, response parsing, and rate-limit handling.

---

## Recap

1. Samsung Wallet Business Portal application submitted & approved
2. Region chosen (US/EU/KR) — matches where your users live and where your test phone's Samsung account is registered
3. RSA keypair generated; public key uploaded to the portal; private key kept local
4. Partner ID (and optional Service ID) retrieved from the portal
5. PHP project with `jolicode/wallet-kit` installed, `.env.local` filled in
6. `test-api.php` → card created → Add-to-Wallet URL opened on Galaxy → card in Samsung Wallet
7. `test-push.php` → device nudged, changes picked up on pull-to-refresh
