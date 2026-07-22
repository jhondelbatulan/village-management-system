# Payment Management Module

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

Version: 1.0

---

# 1. Purpose

This document defines the Payment Management Module of the proposed system.

The module provides a centralized and configurable mechanism for managing different payment schemes implemented by participating villages. Since each village has different payment policies, the system allows payment types, schedules, penalties, and reports to be customized according to village requirements.

The module also integrates with the Predictive Analytics Module to evaluate household payment behavior and generate payment-related insights.

---

# 2. Objectives

The Payment Management Module aims to:

- Record household payment transactions.
- Monitor payment status.
- Manage payment penalties.
- Generate collection reports.
- Support village-specific payment policies.
- Integrate with predictive analytics.
- Provide payment dashboards.
- Improve financial transparency.

---

# 3. Village Payment Configuration

Each village may define its own payment policies.

### Comagascas Resettlement Village

Payment Types

- Monthly House Rental
- Monthly Village Dues

Managed by:

Village Treasurer

---

### La Union (Angel Village)

Payment Types

- Annual Building Permit

Payment Amount

Depends on:

- Lot Size
- Building Type
- Village Policy

Penalties

Late payment penalties are automatically computed according to village policy.

Managed by:

Super Admin (CPDO)

(Current implementation proposal)

---

### Calibunan

Payment configuration shall be added after completion of the village survey.

The system shall allow new payment categories without modifying the database structure.

---

# 4. Payment Workflow

## Comagascas

```

Household

↓

Treasurer Records Payment

↓

Payment Validated

↓

Database Updated

↓

Analytics Updated

↓

Prediction Updated

```

---

## La Union

```

Household

↓

CPDO Super Admin

↓

Updates Payment

↓

Penalty Computed

↓

Payment Recorded

↓

Analytics Updated

↓

Prediction Updated

```

---

# 5. Payment Information

Each payment transaction stores:

- Household
- Village
- Payment Type
- Amount
- Due Date
- Payment Date
- Penalty
- Remarks
- Status
- Recorded By

---

# 6. Payment Status

Each payment may have one of the following statuses:

- Paid
- Pending
- Partially Paid
- Overdue
- Cancelled

The status is updated automatically whenever payment information changes.

---

# 7. Payment History

Every household shall have a complete payment history.

The payment history includes:

- Payment Date
- Payment Type
- Amount
- Penalty
- Collector
- Status
- Official Receipt Number (Future)
- Remarks

Payment history cannot be deleted.

Only corrections are allowed through authorized users.

---

# 8. Penalty Management

The system supports configurable penalties.

Penalty may depend on:

- Number of overdue days
- Village policy
- Payment category

Penalty information includes:

- Penalty Amount
- Reason
- Date Applied
- Updated By

---

# 9. Household Payment Profile

Each household shall display:

-----------------------------------

Household Information

Current Payment Status

Outstanding Balance

Penalty Amount

Payment History

Prediction Label

Participation Rating

-----------------------------------

---

# 10. Payment Dashboard

The dashboard shall display:

- Total Collection
- Monthly Collection
- Annual Collection
- Outstanding Payments
- Overdue Payments
- Collection Rate
- Household Payment Classification

Charts shall be generated using Chart.js.

---

# 11. Payment Analytics

The analytics module shall generate:

Collection by Village

Collection by Month

Collection by Payment Type

Payment Completion Rate

Outstanding Balances

Penalty Statistics

Top Paying Households

Households with Overdue Payments

---

# 12. Predictive Analytics Integration

Historical payment data shall be processed by the Python prediction module.

Prediction categories include:

Good Payer

Average Payer

Delinquent Household

High Risk Household

Payment Forecast

Collection Forecast

Prediction results shall be displayed on:

Dashboard

Household Profile

GIS Marker

Analytics

Reports

---

# 13. Payment Classification

The system shall automatically classify households.

Classification

Excellent Payer

Good Payer

Average Payer

Late Payer

Delinquent

Classification shall update whenever prediction results change.

---

# 14. Notifications

The system shall support payment notifications.

Examples

Upcoming Due Date

Payment Overdue

Penalty Applied

Payment Successfully Recorded

Future implementation may include:

Email

SMS

Mobile Notification

---

# 15. Reports

Authorized users may generate:

Collection Report

Payment Report

Penalty Report

Household Payment Report

Monthly Collection Report

Annual Collection Report

Village Collection Summary

Prediction Report

Reports support:

PDF

Excel

Print

---

# 16. User Permissions

## Super Admin

Can:

- View all village payments
- Manage La Union payments
- View analytics
- View prediction reports
- Generate reports

---

## Admin

Can:

- View village payment reports
- View payment analytics
- Monitor payment statistics

Cannot edit payment records.

---

## Secretary

Can:

- View payment information

Cannot modify payments.

---

## Treasurer

Can:

- Record payments
- Update payments
- Manage dues
- View reports
- View payment history

Applicable to villages where payment responsibility belongs to the Treasurer.

---

## Resident

Can:

- View own payment history
- View payment status
- View penalties
- View prediction label

Cannot modify payment records.

---

# 17. Validation Rules

The system validates:

- Amount cannot be negative.
- Due Date is required.
- Payment Date cannot exceed current date.
- Household must exist.
- Payment Type must exist.
- Duplicate payment entries are not allowed.

---

# 18. Security

Payment records cannot be permanently deleted.

Every payment modification shall generate an Audit Log containing:

- User
- Date
- Previous Value
- New Value
- Action

Only authorized users may update payment records.

---

# 19. Future Enhancements

Future versions may support:

- Official Receipt Printing
- QR Code Payments
- GCash Integration
- Maya Integration
- Bank Transfer
- Online Payment Portal
- Automated Penalty Calculation Rules
- Payment Reminders

---

# 20. Summary

The Payment Management Module provides a flexible and secure solution for managing village-specific financial transactions. By supporting configurable payment policies, integrating predictive analytics, and enforcing Role-Based Access Control, the module enables efficient monitoring of collections while adapting to the unique payment requirements of each participating village.

---

End of Document