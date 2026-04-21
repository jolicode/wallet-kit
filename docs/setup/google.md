# Setup wallet-kit with Google Wallet

This tutorial walks you through testing the Google Wallet integration of `wallet-kit` from scratch, all the way to seeing a pass installed on your Android phone.

Expected time: **~45 minutes** the first time, **~5 minutes** on subsequent runs.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Step 1 — Create your Google Wallet Issuer account](#step-1--create-your-google-wallet-issuer-account)
- [Step 2 — Add your Google account as a Test user](#step-2--add-your-google-account-as-a-test-user)
- [Step 3 — Create the Google Cloud Service Account](#step-3--create-the-google-cloud-service-account)
- [Step 4 — Link the Service Account to the Issuer](#step-4--link-the-service-account-to-the-issuer)
- [Step 5 — Set up the test project](#step-5--set-up-the-test-project)
- [Step 6 — Minimal test script (Save Link)](#step-6--minimal-test-script-save-link)
- [Step 7 — Open the Save Link on your phone](#step-7--open-the-save-link-on-your-phone)
- [Step 8 — Verify on the phone](#step-8--verify-on-the-phone)
- [Step 9 — Test the full API (create class + object)](#step-9--test-the-full-api-create-class--object)
- [Step 10 — Test a pass update](#step-10--test-a-pass-update)
- [Debugging](#debugging)
- [Iterating quickly](#iterating-quickly)
- [Recap](#recap)

---

## Prerequisites

- An Android phone with the **Google Wallet** app installed and a Google account signed in (iOS does not have Google Wallet)
- PHP 8.3+ and Composer installed locally
- The `ext-openssl` PHP extension enabled
- A Google account to create the Wallet issuer and Cloud project

---

## Step 1 — Create your Google Wallet Issuer account

> **Important:** Google Pay Business and Google Wallet are two **separate** products with two **separate** onboardings. Creating a Google Pay Business profile does **not** give you a Wallet Issuer ID. If your dashboard shows an alphanumeric code like `BCR2DN4TTTR7V3ZE`, that's a **Pay merchant code**, not a Wallet Issuer ID. The Wallet Issuer ID is always **16 digits, numeric only** (e.g. `3388000000012345`).

Go to **[pay.google.com/business/console](https://pay.google.com/business/console)** and sign in with your Google account.

In the left sidebar, open **Google Wallet API** (if you don't see it, use the **☰ menu** and look under "More products" / "Explore", or go directly to **[wallet.google.com/business/console](https://wallet.google.com/business/console)**).

On first access, Google asks you to create a **Wallet Issuer** profile — this is the onboarding that produces the 16-digit Issuer ID:

- **Business name**: your name or a test name
- **Website**: any valid URL (a personal site works fine for testing)
- **Contact email**: your email
- **Country**: your country

Accept the Wallet-specific Terms of Service. You land on the Wallet API dashboard.

### Finding the Issuer ID

The Issuer ID shows up once the Wallet Issuer is created. Look for it in any of these places:

- **Top-left of the Wallet API dashboard**, next to or under your business name
- **Settings → General** of the Wallet console
- In the URL of the dashboard page (`issuerId=...`)

Format: **16-digit numeric** string (e.g. `3388000000012345`). Keep it handy — you will use it everywhere.

> **Can't find it?** You almost certainly only completed Google Pay Business onboarding, not Wallet. Revisit the Wallet-specific onboarding above — the Issuer ID only exists after you accept the Wallet ToS.

In Demo mode (the default), you are good to go immediately. No Google review required.

---

## Step 2 — Add your Google account as a Test user

In the Wallet Console, section **Users**:

- Add the Google email **of the account signed in on your Android phone**
- Role: "Developer" is enough

Without this step, your phone will refuse to add passes in Demo mode with a message like "Unable to add this card".

---

## Step 3 — Create the Google Cloud Service Account

Go to **[console.cloud.google.com](https://console.cloud.google.com)**:

1. Create a new project (e.g. `wallet-kit-test`)
2. In the menu, go to **APIs & Services → Library**, search for **Google Wallet API** and enable it
3. Go to **APIs & Services → Credentials → Create Credentials → Service Account**
    - Name: `wallet-kit-test-sa`
    - Leave the rest as default, skip the optional roles, click "Done"
4. Click on the service account you just created → **Keys** tab → **Add Key → Create new key → JSON**
5. Download the JSON file, rename it `google-service-account.json`, and keep it somewhere safe (e.g. `~/.wallet-kit-test/`)

Open the JSON and note the value of the `client_email` field — format `wallet-kit-test-sa@xxx.iam.gserviceaccount.com`.

---

## Step 4 — Link the Service Account to the Issuer

**This critical step is often forgotten.** Go back to the Wallet Console:

- Section **Users → Invite a user**
- Email: the `client_email` of your service account
- Role: "Developer"
- Invite

Without this, all your API requests will return 403.

---

## Step 5 — Set up the test project

Create a test folder:

```bash
mkdir ~/wallet-kit-test && cd ~/wallet-kit-test
```

Copy the `composer.json` template from [`examples/google/composer.json`](../../examples/google/composer.json) into your folder, then install:

```bash
composer install
```

Copy your service account JSON into the folder:

```bash
cp ~/path/to/google-service-account.json ./google-sa.json
```

Then copy the `.env` from [`examples/google/.env`](../../examples/google/.env) to `.env.local` and fill in your values:

```bash
cp .env .env.local
```

> **Where to put your secrets:** `.env` is the committed template with placeholder values — leave it alone. Fill your real values in `.env.local` (gitignored). Symfony Dotenv loads `.env` first and then overlays `.env.local` on top, so `.env.local` always wins.

Required keys:

- `GOOGLE_WALLET_ISSUER_ID` — the 16-digit Issuer ID from Step 1
- `GOOGLE_WALLET_SERVICE_ACCOUNT_PATH` — absolute path to `google-sa.json`
- `GOOGLE_WALLET_CLASS_ID` / `GOOGLE_WALLET_OBJECT_ID` — needed only by `test-update.php`, populated from the values `test-api.php` prints

---

## Step 6 — Minimal test script (Save Link)

Let's start with the simplest path: generate a Save Link without going through the API. A signed JWT that, when opened on the phone, offers "Add to Google Wallet".

Copy [`examples/google/test-save-link.php`](../../examples/google/test-save-link.php) into your folder. The script reads `GOOGLE_WALLET_ISSUER_ID` and `GOOGLE_WALLET_SERVICE_ACCOUNT_PATH` from the `.env` you created in Step 5 — no edits needed.

The script builds an **Offer (coupon) pass** via `WalletPass::offer(...)`. If you want to try another vertical (loyalty, event ticket, transit, …), see `docs/builder-examples.md` for the current builder signatures — if a required parameter is missing, the builder will tell you clearly at runtime.

Run:

```bash
php test-save-link.php
```

If all goes well, you get a URL like `https://pay.google.com/gp/v/save/eyJhbGci...`.

---

## Step 7 — Open the Save Link on your phone

Three options to transfer the URL:

**Option A (fastest)** — generate a QR code in the terminal:

```bash
# macOS/Linux with qrencode installed
echo "URL_PASTED_HERE" | qrencode -t ANSIUTF8
```

Then scan it from your Android camera app.

**Option B** — send the URL to your phone via email or a messenger (Signal/WhatsApp/Messages), then tap it.

**Option C** — save the URL to a local HTML file and open it:

```html
<a href="URL_HERE">Add to Google Wallet</a>
```

---

## Step 8 — Verify on the phone

You should see the Google Wallet "Save to Google Wallet" screen with a preview of the pass. Tap "Save".

If it works → open the Google Wallet app, your pass is in there. 🎉

If it fails, see the [Debugging](#debugging) section below.

---

## Step 9 — Test the full API (create class + object)

The Save Link is handy but only tests `GoogleSaveLinkGenerator` and the JWT signing. To validate `GoogleWalletClient` and `GoogleOAuth2Authenticator`, copy [`examples/google/test-api.php`](../../examples/google/test-api.php) into your folder. It reuses the same `.env` values — no edits needed.

**The benefit**: now that the class and object exist on Google's side, the JWT can be much shorter (it references just `{id, classId}` instead of inlining the full definition), which eliminates any URL length issues.

Run:

```bash
php test-api.php
```

You should see "Class and object created/updated" followed by a shorter URL. Open it on the phone.

**Verify on the Console side**: go back to [pay.google.com/business/console](https://pay.google.com/business/console), section **Classes**. Your class appears with status "Under review" (expected in Demo mode).

---

## Step 10 — Test a pass update

To confirm that `updateObject` works and that changes propagate to the phone, copy [`examples/google/test-update.php`](../../examples/google/test-update.php) into your folder. Before running, set `GOOGLE_WALLET_CLASS_ID` and `GOOGLE_WALLET_OBJECT_ID` in your `.env.local` to the IDs printed by Step 9.

On the phone, open the pass in Google Wallet and pull down to refresh. The changes appear (sometimes with a few seconds of delay).

---

## Debugging

### 403 `The caller does not have permission`

You skipped [Step 4](#step-4--link-the-service-account-to-the-issuer). Go back to Console → Users and add the `client_email`.

### 400 `Invalid class ID` or `not a valid id: <something>.<suffix>`

Two possible causes:

- Your `classId` does not start with `{issuerId}.` — check your string concatenation.
- Your `$issuerId` is not a **16-digit numeric** Wallet Issuer ID. Alphanumeric codes like `BCR2DN4TTTR7V3ZE` are Google Pay merchant codes, not Wallet Issuer IDs. See [Step 1](#step-1--create-your-google-wallet-issuer-account) to create the Wallet Issuer profile.

### 400 `<FieldName> is required` (or similar on other fields)

A required field is missing from your pass. The API response lists the offending field — fill it in on the builder or directly on the underlying model before sending.

### 400 `LoyaltyClass cannot be created without a program logo`

Google enforces a `programLogo` on every `LoyaltyClass`. The cross-platform `LoyaltyWalletBuilder` does not expose it yet, so set it on the underlying model after `build()`:

```php
use Jolicode\WalletKit\Pass\Android\Model\Shared\Image;
use Jolicode\WalletKit\Pass\Android\Model\Shared\ImageUri;

$built = WalletPass::loyalty($context, programName: '...')->build();

$built->google()->issuerClass->programLogo = new Image(
    sourceUri: new ImageUri('https://your.cdn/logo.png'),
);
```

The URL must be publicly reachable by Google's image fetcher (plain `https://...` PNG/JPG). Placeholder hosts like `storage.googleapis.com/...codelab-artifacts...` may 404 — use your own CDN or a stable public asset (e.g. `gstatic.com`).

### The phone says "Unable to add this card"

Three common causes:

- The Google account on your phone is not in the Test users list ([Step 2](#step-2--add-your-google-account-as-a-test-user))
- You used `ReviewStatusEnum::APPROVED`, which is only allowed in Production — switch to `UNDER_REVIEW` or `DRAFT`
- The pass has an expiration date in the past

### The Save Link URL is huge (>2000 chars) and gets truncated

Normal if you inline everything (long text, base64 images). Use the API path first ([Step 9](#step-9--test-the-full-api-create-class--object)) — the JWT will be short.

### Nothing happens when I tap the link from an SMS

The URL was probably truncated by the SMS client. Try sending via email (no length limit) or via QR code.

### `Unable to read private key from Google service account`

The JSON file is corrupted or you pointed to the wrong file. Check:

```bash
cat google-sa.json | head -c 50
# Should start with: {"type":"service_account"
```

### Deep network debugging

To see exactly what goes over the wire to Google, wrap the HTTP client in a `TraceableHttpClient`:

```php
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\TraceableHttpClient;

$http = new TraceableHttpClient(HttpClient::create());

// ... after your API calls ...
foreach ($http->getTracedRequests() as $trace) {
    echo $trace['method'] . ' ' . $trace['url'] . "\n";
    echo 'Status: ' . $trace['info']['http_code'] . "\n\n";
}
```

You can also decode the JWT produced by the Save Link on **[jwt.io](https://jwt.io)** (paste the part after `/save/`). You will see the header and payload in plain text — useful to verify your data is well-formed before even sending it to Google.

---

## Iterating quickly

While you develop, you will want to recreate passes in a loop. To avoid cluttering your issuer:

- Use a unique suffix (`uniqid()` or a timestamp) for each `classId`/`objectId`, as shown in the scripts above
- In the Console, you can delete test classes/objects in bulk

And if you want to test without hitting the Google API every time, run the `wallet-kit` unit tests:

```bash
cd /path/to/wallet-kit
vendor/bin/phpunit tests/Api/Google/
```

They use `MockHttpClient` so there are no network calls, but they validate the request shapes and error handling logic.

---

## Recap

1. Google Wallet Console account + Issuer ID
2. Your phone's Google account added as a Test user
3. Google Cloud service account + JSON key downloaded
4. Service account linked to the issuer ← **critical**
5. PHP project with `jolicode/wallet-kit` installed
6. Save Link script → URL opened on Android → pass added
7. API script → `createOrUpdatePass` → minimal Save Link
8. Update script → changes visible after refresh