# Role-Based Workflow

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

**Version:** 1.0

---

# 1. Purpose

This document defines the workflow processes implemented in the system.

The workflow ensures that every operation follows a structured sequence of responsibilities based on Role-Based Access Control (RBAC). Each user performs only the tasks assigned to their role while maintaining accountability, data integrity, and security.

The workflow model is based on the principle that information should pass through authorized users before becoming official records within the system.

---

# 2. Workflow Overview

The proposed system follows a hierarchical workflow.

```

Super Admin (CPDO)

↓

Admin (Village President)

↓

Secretary

↓

Treasurer

↓

Resident

```

Every operation within the system follows this hierarchy.

---

# 3. Account Management Workflow

The system does not provide public registration.

Only authorized users may create accounts.

## Workflow

```

Super Admin

↓

Creates Admin Account

↓

Assigns Village

↓

Admin Logs In

↓

Creates Secretary Account

↓

Creates Treasurer Account

↓

Creates Resident Accounts

↓

Residents Receive Login Credentials

```

### Rules

- One Admin per village.

- Admin can only create users within his/her assigned village.

- Secretary cannot create accounts.

- Treasurer cannot create accounts.

- Residents cannot create accounts.

---

# 4. Resident Registration Workflow

Resident records shall follow an approval process.

```

Secretary

↓

Encodes Resident Information

↓

Admin Reviews

↓

Resident Record Approved

↓

Resident becomes Active

```

### Benefits

- Prevents duplicate records.

- Ensures data accuracy.

- Maintains updated resident information.

---

# 5. Household Management Workflow

Each household shall belong to only one village.

```

Secretary

↓

Registers Household

↓

Adds Household Members

↓

Admin Reviews

↓

Household Activated

```

Every resident shall belong to one household.

---

# 6. Village Profile Workflow

The Secretary maintains village information.

```

Secretary

↓

Updates Village Profile

↓

Uploads Village Documents

↓

Updates Organizational Structure

↓

Admin Reviews

↓

Changes Become Visible

```

Village information includes:

- Village Description

- Village History

- Officer Profiles

- Organizational Structure

- Village Documents

---

# 7. GIS Workflow

Household locations are managed through GIS.

```

Secretary/Admin

↓

Registers Household

↓

Assigns GPS Coordinates

↓

Stores Latitude & Longitude

↓

Leaflet.js Displays Marker

↓

Users View Household Location

```

Each household marker displays:

- Household Number

- Household Head

- Address

- Payment Classification

- Participation Status

---

# 8. Payment Workflow

The payment workflow depends on village policies.

---

## 8.1 Comagascas Village

```

Treasurer

↓

Records Monthly Rental

↓

Records Monthly Dues

↓

Updates Payment Status

↓

Payment History Updated

↓

Analytics Updated

```

---

## 8.2 La Union (Angel Village)

(Current implementation proposal)

```

Super Admin (CPDO)

↓

Updates Building Permit Payment

↓

Calculates Penalty

↓

Updates Payment Status

↓

Analytics Updated

```

Building permit amount depends on:

- Lot Size

- Building Type

- Village Policy

Possible statuses:

- Paid

- Pending

- Overdue

---

## 8.3 Calibunan

Payment workflow will be configured after completion of the village survey.

---

# 9. Announcement Workflow

```

Admin

or

Secretary

↓

Creates Announcement

↓

Residents Receive Notification

↓

Resident Views Announcement

↓

Resident Confirms Participation

↓

Secretary Verifies

↓

Attendance Recorded

```

Announcement categories include:

- Community Meetings

- Emergency Notices

- Events

- Clean-up Activities

- Government Announcements

---

# 10. Participation Monitoring Workflow

Participation monitoring is integrated with announcements.

```

Announcement Published

↓

Resident Clicks

"I Will Attend"

↓

Status

Pending

↓

Secretary Verifies Attendance

↓

Approved

↓

Attendance Recorded

↓

Analytics Updated

```

Super Admin and Admin can monitor:

- Attendance Rate

- Household Participation

- Village Participation

---

# 11. Predictive Analytics Workflow

Prediction begins after sufficient historical data has been collected.

```

Historical Payment Data

↓

Python Data Processing

↓

Machine Learning Model

↓

Prediction Generated

↓

Prediction Stored

↓

Dashboard Updated

```

Prediction categories:

- Good Payer

- Average Payer

- Delinquent Household

- Collection Forecast

- Participation Trend

---

# 12. Data Analytics Workflow

```

Resident Data

↓

Household Data

↓

Payment Data

↓

Participation Data

↓

Laravel Queries

↓

Chart.js

↓

Dashboard

```

Analytics include:

- Population

- Gender

- Age

- Occupation

- Educational Attainment

- Civil Status

- Household Count

- Payment Collection

- Participation Statistics

---

# 13. Report Generation Workflow

```

User Requests Report

↓

System Retrieves Data

↓

Formats Report

↓

Generate PDF / Excel

↓

Download

```

Reports available:

- Resident Report

- Household Report

- Village Report

- Payment Report

- Participation Report

- Analytics Report

---

# 14. Audit Logging Workflow

Every important action shall be recorded.

```

User Action

↓

Audit Logger

↓

Database

↓

Audit Trail

```

Recorded activities include:

- Login

- Logout

- Create

- Update

- Delete

- Payment Update

- Approval

- Account Creation

---

# 15. Workflow Security

The workflow follows the following security principles.

- Authentication Required

- Authorization Required

- Role-Based Access Control

- Least Privilege Principle

- Audit Logging

- Session Management

- Secure Password Hashing

No workflow shall bypass authorization.

---

# 16. Workflow Summary

The Role-Based Workflow ensures that all system processes follow a structured hierarchy from Super Admin down to Resident. Every module—including account management, resident registration, GIS visualization, payment monitoring, announcements, participation tracking, analytics, and predictive analytics—follows defined approval and authorization procedures.

This workflow promotes accountability, improves data quality, and supports secure and efficient management of multiple villages under the City Planning and Development Office (CPDO).

---

**End of Document**