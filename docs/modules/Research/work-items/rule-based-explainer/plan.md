# Plan: Rule-based Research Explainer

## Scope

Membangun explainer awal berbasis aturan untuk `/jelaskan-nilai` tanpa live API
baru dan tanpa AI.

## Increment 1: Payload backend

- Perubahan: service Research membaca snapshot Company dan membentuk explanation
  payload.
- Prasyarat: `FakeCompanySnapshot`.
- Acceptance: symbol fake dan pending sama-sama menghasilkan payload eksplisit.
- Verifikasi: unit/feature test.

## Increment 2: UI frontend

- Perubahan: dashboard explainer dengan input ticker, summary cards, evidence
  table, dan guardrail non-rekomendasi.
- Prasyarat: payload Research.
- Acceptance: typecheck, lint, build lulus.
- Verifikasi: frontend checks.

## Batas berhenti dan pemulihan

Berhenti setelah `/jelaskan-nilai` usable. Integrasi AI/scoring real dibuat work
item terpisah.
