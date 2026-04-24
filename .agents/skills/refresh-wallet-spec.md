---
name: refresh-wallet-spec
description: Reconcile PHP wallet models with upstream spec changes. Detect → diff → decide → patch → baseline → verify.
disable-model-invocation: true
---

Do not skip or reorder. Order prevents silently re-baselining a real regression.

## 1. Detect

```bash
castor spec:check:google
castor spec:check:apple
castor spec:check:samsung
```

All pass → stop.

## 2. Diff (failing providers only)

```bash
castor spec:diff:google --properties
# Apple:   tools/spec/apple-pass-keyset.json    vs src/Pass/Apple/**
# Samsung: tools/spec/samsung-wallet-keyset.json vs src/Pass/Samsung/**
```

Large diff → dispatch `spec-drift-investigator`.

## 3. Decide per field

| Situation | Action |
|---|---|
| Upstream added, we want it | Patch DTO + Builder + fixture → re-baseline |
| Upstream removed/renamed, we use it | Patch models + callers → re-baseline |
| Upstream enum/type change | Patch models → re-baseline |
| Baseline stale, models correct | Re-baseline only |
| Drift looks unintentional | Stop. Escalate. Do NOT re-baseline. |

**Never** re-baseline before patching — it hides the drift.

## 4. Patch

Edit `src/Pass/<Provider>/**`, `src/Builder/<Provider>/**`, `tests/Builder/<Provider>/**`. `tools/spec/*.json` are hook-blocked by design.

## 5. Re-baseline

Only after step 4, only for intentional drift:

```bash
castor spec:baseline:{google|apple|samsung}
```

## 6. Verify

```bash
vendor/bin/phpunit
castor qa:phpstan
castor qa:cs:check
castor spec:check:{google,apple,samsung}
```

All green → commit model changes, fixtures, and refreshed baseline **together** so provenance shows in `git log`.
