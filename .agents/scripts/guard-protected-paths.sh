#!/usr/bin/env bash
# Blocks edits to generated spec baselines and local credential files.
#
# Protocol: reads a JSON tool-call payload on stdin with at least
#   { "tool_name": "...", "tool_input": { "file_path": "..." } }
# On a protected path: prints reason to stderr, exits 2 (hook block).
# Otherwise: exits 0.
#
# Wired into Claude Code via .claude/settings.json (PreToolUse).
# Cursor users: this script follows the same stdin/exit-code contract
# and can be reused from a Cursor hook when available. See
# .cursor/rules/protected-files.mdc for the soft-block fallback.

set -euo pipefail

payload="$(cat)"

tool=$(printf '%s' "$payload" | sed -n 's/.*"tool_name"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')
path=$(printf '%s' "$payload" | sed -n 's/.*"file_path"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p')

case "$tool" in
    Edit|Write|MultiEdit|NotebookEdit) ;;
    *) exit 0 ;;
esac

[ -z "$path" ] && exit 0

case "$path" in
    */tools/spec/*.json|tools/spec/*.json)
        echo "BLOCKED: $path is a generated spec baseline." >&2
        echo "Refresh it with 'castor spec:baseline:google|apple|samsung' instead of hand-editing." >&2
        exit 2
        ;;
    */.env.local|*.env.local|.env.local)
        echo "BLOCKED: $path holds local credentials and must not be edited by the agent." >&2
        echo "Ask the human operator to update it manually." >&2
        exit 2
        ;;
esac

exit 0
