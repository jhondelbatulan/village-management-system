# Resident and Household Management Module

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

Version: 1.0

---

# 1. Purpose

This document defines the Resident and Household Management Module of the proposed system.

This module serves as the primary repository of household and resident information for all participating villages under the City Planning and Development Office (CPDO). It provides centralized management of demographic data while supporting GIS visualization, payment monitoring, participation tracking, and predictive analytics.

All resident and household records shall follow Role-Based Workflow and Role-Based Access Control (RBAC).

---

# 2. Objectives

The module aims to:

- Centralize resident information
- Centralize household records
- Eliminate duplicate records
- Improve data accuracy
- Maintain updated demographic information
- Support GIS visualization
- Support data analytics
- Support predictive analytics

---

# 3. Household Management

A household represents one occupied residential unit within a village.

Each household belongs to only one village.

Each household contains one or more residents.

---

## Household Information

The system shall store the following information.

- Household Number
- Household Head
- Village
- Purok / Zone
- Complete Address
- Latitude
- Longitude
- Contact Number
- Payment Classification
- Participation Rating
- Household Status
- Date Registered

---

## Household Status

Possible household status:

- Active
- Inactive
- Relocated
- Archived

---

# 4. Resident Management

Every resident shall belong to only one household.

The system shall maintain comprehensive demographic information.

---

## Resident Information

The system stores:

- Resident Code
- First Name
- Middle Name
- Last Name
- Suffix
- Gender
- Birthdate
- Age
- Civil Status
- Occupation
- Educational Attainment
- Monthly Income
- Contact Number
- Email
- Voter Status
- Household Relationship
- Household Head Indicator
- Status

---

## Resident Status

Residents may have the following status.

- Active
- Inactive
- Deceased
- Transferred
- Archived

---

# 5. Resident Registration Workflow

The resident registration process follows a controlled workflow.

```

Secretary

↓

Encodes Resident Information

↓

System Validation

↓

Admin Reviews

↓

Resident Approved

↓

Resident Activated

```

This workflow ensures data integrity and prevents unauthorized records.

---

# 6. Household Registration Workflow

The household registration process consists of the following steps.

```

Secretary/Admin

↓

Create Household

↓

Assign Household Number

↓

Assign GPS Coordinates

↓

Add Household Head

↓

Add Household Members

↓

Save Household

↓

Household Becomes Active

```

---

# 7. Household Profile

Each household has a dedicated profile page containing:

Household Information

↓

GIS Location

↓

Household Members

↓

Payment History

↓

Participation History

↓

Prediction Result

↓

Documents (Future)

---

# 8. Resident Profile

Each resident has a profile page displaying:

- Personal Information
- Household Information
- Participation History
- Payment Information (if applicable)
- Announcement Responses
- Profile Picture
- Contact Information

---

# 9. Household Search

Users may search households using:

- Household Number
- Household Head
- Village
- Address
- Purok
- Payment Status

Search results shall update instantly.

---

# 10. Resident Search

Users may search residents by:

- Resident Code
- Name
- Household
- Gender
- Occupation
- Civil Status

Advanced filtering shall also be supported.

---

# 11. Filtering Options

The system shall provide filtering by:

Village

Household Status

Resident Status

Gender

Civil Status

Occupation

Educational Attainment

Payment Classification

Participation Status

Prediction Label

---

# 12. Household Transfer

If a household transfers to another village, the workflow shall be:

```

Secretary

↓

Requests Transfer

↓

Admin Reviews

↓

Super Admin Confirms

↓

Household Moved

↓

GIS Updated

↓

Analytics Updated

```

---

# 13. Resident Transfer

Residents may transfer to another household.

Workflow:

```

Secretary

↓

Select Resident

↓

Choose New Household

↓

Update Relationship

↓

Save Changes

↓

Household Statistics Updated

```

---

# 14. Household GIS Integration

Every household stores:

Latitude

Longitude

These coordinates are used by Leaflet.js.

Clicking the marker displays:

- Household Number
- Household Head
- Household Members
- Payment Classification
- Participation Status
- Prediction Label

---

# 15. Household Payment Classification

Each household shall automatically receive a payment classification.

Possible classifications:

- Good Payer
- Average Payer
- Delinquent
- Pending Evaluation

These classifications are updated by the predictive analytics module.

---

# 16. Household Participation Rating

The system calculates participation based on approved attendance records.

Possible ratings:

- Highly Active
- Active
- Moderately Active
- Low Participation
- Inactive

These ratings are displayed on:

- Household Profile
- GIS Marker
- Dashboard
- Analytics

---

# 17. Import and Export

Authorized users may:

Import

- Excel
- CSV

Export

- Excel
- PDF

The import process validates:

- Duplicate Resident Codes
- Duplicate Household Numbers
- Missing Required Fields

---

# 18. Validation Rules

The system validates the following.

Resident Code

Unique

Household Number

Unique within a village

Birthdate

Cannot exceed current date

Latitude

Required

Longitude

Required

Village

Required

Household Head

Only one per household

---

# 19. Dashboard Statistics

The module provides:

Total Residents

Total Households

Average Household Size

Gender Distribution

Age Distribution

Civil Status Distribution

Occupation Distribution

Educational Attainment

Payment Classification Summary

Participation Summary

---

# 20. Security

Only authorized users may:

Create Household

Update Household

Delete Household

Archive Household

Create Resident

Update Resident

Archive Resident

Delete Resident

All activities are recorded in Audit Logs.

---

# 21. Future Enhancements

Possible future improvements include:

QR Code Resident ID

National ID Verification

Biometric Registration

Birth Certificate Upload

Document Verification

Household Photo Gallery

Mobile Registration

Offline Registration

---

# 22. Summary

The Resident and Household Management Module serves as the foundation of the proposed Web-Based GIS-Enabled Multi-Village Management System. It centralizes demographic records while supporting GIS visualization, payment monitoring, participation tracking, analytics, and predictive analytics. Through controlled workflows and RBAC, the module ensures data accuracy, security, and efficient management of households across multiple villages.

---

End of Document