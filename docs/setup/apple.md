# Setup wallet-kit with Apple Wallet

This tutorial walks you through testing the Apple Wallet integration of `wallet-kit` from scratch, all the way to seeing a pass installed on your iPhone.

Expected time: **~45 minutes** the first time, **~5 minutes** on subsequent runs.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Step 1 — Apple Developer Program enrollment](#step-1--apple-developer-program-enrollment)
- [Step 2 — Register a Pass Type Identifier](#step-2--register-a-pass-type-identifier)
- [Step 3 — Generate the Pass Type ID certificate](#step-3--generate-the-pass-type-id-certificate)
- [Step 4 — Export the certificate as .p12](#step-4--export-the-certificate-as-p12)
- [Step 5 — Prepare the icon](#step-5--prepare-the-icon)
- [Step 6 — Set up the test project](#step-6--set-up-the-test-project)
- [Step 7 — Package and sign a .pkpass](#step-7--package-and-sign-a-pkpass)
- [Step 8 — Install the pass on your iPhone](#step-8--install-the-pass-on-your-iphone)
- [Step 9 — (Optional) Push update via APNs](#step-9--optional-push-update-via-apns)
- [Debugging](#debugging)
- [Iterating quickly](#iterating-quickly)
- [Recap](#recap)

---

## Prerequisites

- An iPhone with **Apple Wallet** installed (stock on iOS)
- A paid **Apple Developer Program** membership ($99/year) — unlike Google, there is no free demo mode
- PHP 8.3+ and Composer installed locally
- The `ext-openssl` and `ext-zip` PHP extensions enabled
- MacOS Keychain Access (easiest path for .p12 export) — Linux/Windows alternative shown below

---

## Step 1 — Apple Developer Program enrollment

Go to **[developer.apple.com](https://developer.apple.com/account/)** and sign in with your Apple ID.

If you are not enrolled yet, click **Account → Enroll** and complete the Developer Program signup. Individual enrollment takes a few minutes; Organization enrollment can take several days (D-U-N-S verification).

Once enrolled, from **Account → Membership**, note your **Team ID** (10 characters, alphanumeric, e.g. `A1B2C3D4E5`). You will use it everywhere.

---

## Step 2 — Register a Pass Type Identifier

In **[Certificates, Identifiers & Profiles](https://developer.apple.com/account/resources/identifiers/list/passTypeId)**:

- Sidebar: **Identifiers** → filter by **Pass Type IDs** (top-right dropdown)
- Click the blue **+** to register a new one
- Description: any label (e.g. `WalletKit test coupon`)
- Identifier: must start with `pass.` — convention is reverse-DNS (e.g. `pass.com.example.walletkit-test`)
- Register

The Pass Type Identifier is now listed. Keep it around — you will put it in `.env.local`.

---

## Step 3 — Generate the Pass Type ID certificate

Still in the **Pass Type IDs** list, click the identifier you just created, then **Create Certificate**.

Apple asks for a **Certificate Signing Request (CSR)**. Generate one on your machine:

### macOS — Keychain Access

1. Open **Keychain Access** → menu **Certificate Assistant → Request a Certificate From a Certificate Authority**
2. Email: your Apple ID email
3. Common Name: anything (e.g. `WalletKit Pass Type ID`)
4. **Saved to disk**, continue, save as `passtype.certSigningRequest`

### Linux / Windows — OpenSSL

```bash
openssl genrsa -out passtype.key 2048
openssl req -new -key passtype.key -out passtype.certSigningRequest \
    -subj "/emailAddress=you@example.com/CN=WalletKit Pass Type ID/C=US"
```

Keep `passtype.key` — you will need it at export time.

### Back in the Apple portal

Upload the `.certSigningRequest` file, click **Continue**, then **Download**. You get a `pass.cer` file.

---

## Step 4 — Export the certificate as .p12

`wallet-kit` expects a single `.p12` file that bundles the certificate + its private key.

### macOS — Keychain Access

1. Double-click the downloaded `pass.cer` → it imports into Keychain under **login → My Certificates**
2. Expand the row — you should see a private key named after the CSR common name
3. Right-click the certificate → **Export "Pass Type ID: pass.com.example..."**
4. File format: **Personal Information Exchange (.p12)**
5. Set a password (can be empty) and save as `pass-type-id.p12`

### Linux / Windows — OpenSSL

Convert Apple's DER certificate and bundle it with the private key:

```bash
openssl x509 -in pass.cer -inform DER -out passtype.crt -outform PEM
openssl pkcs12 -export \
    -out pass-type-id.p12 \
    -inkey passtype.key \
    -in passtype.crt \
    -name "Pass Type ID"
```

You will be prompted for an export password (can be empty).

> **Important:** you do NOT need a separate Apple WWDR certificate — `wallet-kit` ships `AppleWWDRCAG4.cer` at `src/Api/Apple/Resources/` and uses it by default. If you need a different WWDR (e.g. legacy G3), pass `wwdrCertificatePath` to `AppleCredentials`.

---

## Step 5 — Prepare the icon

Apple requires at minimum an `icon.png` in every `.pkpass` bundle. Minimum size is 29x29; the recommended set is 29/58/87 for @1x/@2x/@3x.

Any PNG works for a first test. If you don't have one, generate a placeholder:

```bash
# macOS
sips -s format png --resampleHeightWidth 58 58 /System/Library/CoreServices/Installer.app/Contents/Resources/Installer.icns --out icon.png
# or just pick any small PNG you have around
```

Note its absolute path — you will put it in `.env.local`.

---

## Step 6 — Set up the test project

Create a test folder:

```bash
mkdir ~/wallet-kit-apple-test && cd ~/wallet-kit-apple-test
```

Copy the `composer.json` template from [`examples/apple/composer.json`](../../examples/apple/composer.json) into your folder, then install:

```bash
composer install
```

Copy your certificate into the folder and keep it readable only by you:

```bash
cp ~/path/to/pass-type-id.p12 ./pass-type-id.p12
chmod 600 pass-type-id.p12
```

Then copy [`examples/apple/.env`](../../examples/apple/.env) to `.env.local` and fill in your values:

```bash
cp .env .env.local
```

> **Where to put your secrets:** `.env` is the committed template with placeholder values — leave it alone. Fill your real values in `.env.local` (gitignored). Symfony Dotenv loads `.env` first and then overlays `.env.local` on top, so `.env.local` always wins.

Required keys:

- `APPLE_TEAM_IDENTIFIER` — 10-char Team ID from Step 1
- `APPLE_PASS_TYPE_IDENTIFIER` — the `pass.*` string from Step 2
- `APPLE_CERTIFICATE_PATH` — absolute path to `pass-type-id.p12`
- `APPLE_CERTIFICATE_PASSWORD` — what you set in Step 4 (empty if none)
- `APPLE_ICON_PATH` — absolute path to the PNG from Step 5

APNS keys (Step 9) are optional for the first run.

---

## Step 7 — Package and sign a .pkpass

Copy [`examples/apple/test-packager.php`](../../examples/apple/test-packager.php) into your folder. It reads everything from `.env.local` — no edits needed.

The script builds an **Offer (coupon) pass** via `WalletPass::offer(...)` and writes `sample.pkpass` next to itself. If you want to try another vertical (loyalty, event ticket, store card, …), swap the `WalletPass::offer(...)` call — the builder reports missing required parameters clearly at runtime.

Run:

```bash
php test-packager.php
```

Expected output:

```
✓ Wrote 3,842 bytes to:
   /Users/you/wallet-kit-apple-test/sample.pkpass
```

If the file is around 3–5 KB and the script did not throw, the manifest was signed with your certificate and the bundle is well-formed.

---

## Step 8 — Install the pass on your iPhone

Three options to get `sample.pkpass` onto the phone:

**Option A (easiest on macOS)** — AirDrop:

```bash
open -R sample.pkpass
# Right-click → Share → AirDrop → your iPhone
```

**Option B** — email `sample.pkpass` to yourself as an attachment, then tap it in Mail on the phone.

**Option C** — serve it over HTTPS with the right MIME type:

```bash
php -S 0.0.0.0:8000
# On the phone, open http://your-laptop-ip:8000/sample.pkpass
# Safari will recognize it if served as application/vnd.apple.pkpass
```

Safari (or Mail) should show an "Add to Apple Wallet" screen with a preview of the pass. Tap **Add**.

Open the Apple Wallet app — your pass is there. 🎉

If it fails, see the [Debugging](#debugging) section below.

---

## Step 9 — (Optional) Push update via APNs

Apple Wallet pull-to-refresh only fires *after* APNs notifies the device that an update is available. To send that notification, you need an **APNs auth key**.

### Generate the .p8 key

In **[Certificates, Identifiers & Profiles → Keys](https://developer.apple.com/account/resources/authkeys/list)**:

- Click **+**
- Name: `wallet-kit APNs`
- Check **Apple Push Notifications service (APNs)**
- Register → Download the `.p8` file (you can only download it once)
- Note the **Key ID** (10 characters, next to the key in the list)

### Web Service registration (not covered by this tutorial)

APNs can only push to a `pushToken` that a device registered against your Web Service. Implementing the Web Service (`webServiceURL` in the pass) is out of scope here — see `src/Bundle/Controller/Apple/AppleWebServiceController.php` for the reference implementation, and the `wallet-kit` bundle docs for how to wire it up.

Once a device has registered, it will POST to your `v1/devices/{deviceId}/registrations/{passTypeId}/{serial}` endpoint with `{"pushToken": "..."}`. Store that token.

### Test the push

Fill these in `.env.local`:

- `APPLE_APNS_KEY_PATH` — absolute path to the downloaded `.p8` file
- `APPLE_APNS_KEY_ID` — the 10-char Key ID
- `APPLE_PUSH_TOKEN` — a real token from your Web Service
- `APPLE_PASS_SERIAL_NUMBER` — the serial whose pass should refresh

Copy [`examples/apple/test-push.php`](../../examples/apple/test-push.php) and run:

```bash
php test-push.php
```

A `✓ APNS accepted the notification` means Apple's servers have queued the push. The device will then call your Web Service `GET /v1/passes/{passTypeId}/{serial}` expecting the updated `.pkpass` back.

---

## Debugging

### `Unable to read certificate from ...` or `openssl_pkcs12_read failed`

The `.p12` password is wrong, or the file is corrupted. Verify with OpenSSL:

```bash
openssl pkcs12 -in pass-type-id.p12 -info -nokeys
# Prompts for the password — if it says "Mac verify error", the password is wrong.
```

### iOS says "Safari cannot download this file" or "Cannot add pass"

Most common causes, in order:

1. **Team ID / Pass Type ID mismatch** — the `passTypeIdentifier` in `pass.json` must exactly match the one the certificate was issued for, and `teamIdentifier` must match your Team ID. Double-check `.env.local`.
2. **Wrong certificate** — you used a generic iOS distribution cert, not a Pass Type ID cert. Only certs issued from the Pass Type IDs section work.
3. **Expired certificate** — Pass Type ID certs are valid 1 year. Renew in the portal.
4. **Missing or unreadable `icon.png`** — iOS silently refuses passes without it. Confirm with `unzip -l sample.pkpass | grep icon`.

### Inspecting the generated bundle

`.pkpass` is just a ZIP. Open it up:

```bash
unzip -o sample.pkpass -d sample/
cat sample/pass.json | jq .
cat sample/manifest.json | jq .
```

You should see `pass.json`, `manifest.json`, `signature`, and `icon.png`. The signature is a DER-encoded PKCS#7 blob — verify with:

```bash
openssl smime -verify \
    -in sample/signature -inform DER \
    -content sample/manifest.json \
    -noverify \
    -out /dev/null
```

`Verification successful` means the signing chain is intact.

### `The "openssl"/"zip" PHP extension is required`

Install the missing extension (`apt install php8.3-zip`, `brew reinstall php`, etc.) and re-run.

### The pass adds, but text/colors look wrong

`WalletPass::*` maps cross-platform fields to Apple's `passType` (coupon/storeCard/eventTicket/generic/boardingPass). If a field is not exposed by the cross-platform builder, set it on the underlying model after `build()`:

```php
$built = WalletPass::offer(...)->build();
$apple = $built->apple();
$apple->foregroundColor = 'rgb(255, 255, 255)';
// ... then pass $apple to the packager
```

---

## Iterating quickly

While you develop, you will want to rebuild passes in a loop:

- Use a unique `serialNumber` per run (the script already uses a timestamp). A stable serial across runs lets the same entry in Wallet be updated via APNs instead of adding duplicates.
- `.pkpass` is cached aggressively by iOS — if the pass does not visually update after re-adding, delete it from Wallet first.

And to test without needing a certificate at all, run the `wallet-kit` unit tests:

```bash
cd /path/to/wallet-kit
vendor/bin/phpunit tests/Api/Apple/
```

They generate self-signed test certificates on the fly and validate the manifest / ZIP structure end-to-end.

---

## Recap

1. Apple Developer Program account + Team ID
2. Pass Type Identifier registered in the portal
3. CSR uploaded → `pass.cer` downloaded → exported as `.p12`
4. `icon.png` prepared (any PNG for a first run)
5. PHP project with `jolicode/wallet-kit` installed, `.env.local` filled in
6. Packager script → `sample.pkpass` on disk → AirDrop to iPhone → added to Wallet
7. (Optional) APNs key + Web Service → `test-push.php` → device refresh
