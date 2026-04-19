# e-Tuntutan (Insurance Claim System) - CLAB

## Tech Stack
- **Framework**: Laravel 12 + Livewire 4
- **UI**: FluxUI + Tailwind CSS
- **Database**: MySQL/MariaDB
- **Auth**: Spatie Permission (role-based access)
- **Notifications**: Email (auto-notify PICs)

## Project Overview
Web-based insurance claim management system (e-Tuntutan) for CLAB (Construction Labour Exchange Centre Berhad). Handles foreign worker insurance claims from employer submission through payment and case closure.

## Claim Types
Three claim categories, all follow the same workflow:
1. **Insurance (FWHS)** — Foreign Worker Hospitalization Scheme
2. **Green Card** — Construction industry insurance
3. **PERKESO** — Social security (SOCSO equivalent)

## System Process Flow

### Phase 1: Application & Document Collection
1. Employer submits claim application (selects claim type)
2. System notifies employer of eligibility and required documents via email
3. Automatic email notification sent to PICs (Person-In-Charge)
4. Employer uploads/updates complete worker information
5. System determines if worker is Outsource or Existing

### Phase 2: Review & Approval
1. CLAB receives application documents from employer
2. CLAB keeps a copy and submits original to insurance company
3. Insurance company reviews the claim
4. **If Approved**: Approval letter issued for payment processing
5. **If Rejected**: Claim forwarded to PERKESO or Green Card for processing
6. PERKESO/Green Card reviews → if approved, approval letter issued
7. Auto email notification to PICs to contact employer and update system status

### Phase 3: Payment & Closure
1. Await compensation payment from insurance/PERKESO/Green Card
2. Payment disbursed to beneficiaries (heirs/next of kin)
3. Employer/insurance notifies CLAB of payment receipt
4. CLAB updates status in system and files the case
5. Case closed

## Required Documents

### Hospitalization Claims
- Accident FCL Form
- Original receipt
- Discharge note
- Doctor's letter

### Death Claims
- Death certificate
- Body delivery receipt (resit penghantaran mayat)
- Police report
- Embassy letter (surat kedutaan)

### Payment Stage Documents
- Approval letter (surat pemakluman)
- Payment slip (slip bayaran)
- Passport copy (salinan pasport)
- Beneficiary bank details (maklumat bank waris)
- Beneficiary information (maklumat waris)

## Status System
Claims use color-coded statuses:
- 🔴 **Open** (Red) — New/pending claims
- 🟡 **In Progress** (Yellow) — Being processed
- 🟢 **Closed** (Green) — Completed/resolved

## PIC (Person-In-Charge) List
Auto email notifications go to: En Razali, En Razi, Puan Winda, Puan Farah, En Seffri

## Key Features to Build
- Employer claim submission form with document upload
- Claim type selection (Insurance/Green Card/PERKESO)
- Worker information form (outsource vs existing worker lookup)
- Document checklist per claim type (hospitalization vs death)
- Automated email notifications to PICs on status changes
- Status tracking dashboard with color-coded indicators
- Approval/rejection workflow with forwarding logic
- Payment tracking and beneficiary management
- File/case closure workflow
- Admin dashboard showing open/in-progress/closed claims

## Conventions
- Use Livewire 3 full-page components for each major module
- Use FluxUI components (flux:modal, flux:table, flux:button, etc.)
- Follow existing booking system patterns for CRUD operations
- Use Spatie Permission for role-based access (admin, PIC, employer)
- All forms use Livewire validation
- Email notifications via Laravel Mail/Notification
- Bilingual support: English + Bahasa Malaysia
