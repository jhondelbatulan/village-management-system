# Database Schema

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

Version: 1.0

---

# 1. Purpose

This document defines the complete database structure of the system.

The database is designed to support centralized multi-village management while ensuring scalability, maintainability, data integrity, and efficient querying.

The system uses MySQL as its relational database management system and Laravel Eloquent ORM for database interaction.

---

# 2. Database Design Principles

The database shall follow the following principles:

- Third Normal Form (3NF)

- Primary Keys

- Foreign Keys

- Referential Integrity

- Cascade Relationships

- Soft Deletes

- Timestamp Tracking

Laravel migrations shall be used to create every table.

---

# 3. Database Overview

The database consists of the following modules.

```

Authentication

↓

Users

↓

Villages

↓

Households

↓

Residents

↓

Payments

↓

Participation

↓

Announcements

↓

GIS

↓

Analytics

↓

Prediction

↓

Reports

```

---

# 4. Database Tables

The system shall contain the following tables.

| No | Table Name |

|----|------------|

|1|roles|

|2|permissions|

|3|role_permission|

|4|users|

|5|villages|

|6|village_officers|

|7|households|

|8|residents|

|9|family_members|

|10|payment_types|

|11|payments|

|12|penalties|

|13|announcements|

|14|announcement_responses|

|15|participation_records|

|16|prediction_results|

|17|audit_logs|

|18|activity_logs|

---

# 5. Table Definitions

---

## 5.1 Roles

Purpose

Stores all system roles.

Fields

- id

- role_name

- description

- created_at

- updated_at

Example

Super Admin

Admin

Secretary

Treasurer

Resident

---

## 5.2 Permissions

Purpose

Stores every available permission.

Fields

- id

- permission_name

- description

Examples

Create User

Delete User

Manage Payments

View GIS

Manage Residents

View Analytics

Generate Reports

---

## 5.3 Role Permission

Purpose

Assigns permissions to each role.

Fields

- id

- role_id

- permission_id

Relationship

Role

↓

Many Permissions

---

## 5.4 Users

Purpose

Stores user login information.

Fields

- id

- role_id

- village_id (nullable for Super Admin)

- username

- password

- first_name

- middle_name

- last_name

- suffix

- profile_picture

- contact_number

- email

- status

- last_login

- created_at

- updated_at

- deleted_at

Relationship

Role

↓

Users

Village

↓

Users

---

## 5.5 Villages

Purpose

Stores village information.

Fields

- id

- village_name

- description

- address

- latitude

- longitude

- total_households

- total_population

- established_date

- logo

- created_at

- updated_at

Current Villages

Comagascas

La Union (Angel Village)

Calibunan

---

## 5.6 Village Officers

Purpose

Stores organizational structure.

Fields

- id

- village_id

- position

- full_name

- photo

- term_start

- term_end

Examples

President

Secretary

Treasurer

Councilor

---

## 5.7 Households

Purpose

Stores household information.

Fields

- id

- village_id

- household_number

- household_head

- purok

- address

- latitude

- longitude

- payment_status

- prediction_label

- remarks

- created_at

- updated_at

Prediction Label Examples

Good Payer

Average Payer

Delinquent

Inactive

---

## 5.8 Residents

Purpose

Stores resident records.

Fields

- id

- household_id

- resident_code

- first_name

- middle_name

- last_name

- gender

- birthdate

- age

- civil_status

- occupation

- educational_attainment

- monthly_income

- contact_number

- email

- voter_status

- is_household_head

- status

- created_at

- updated_at

---

## 5.9 Family Members

Purpose

Stores household family members.

Fields

- id

- resident_id

- relationship

- full_name

- birthdate

- occupation

---

## 5.10 Payment Types

Purpose

Stores configurable payment categories.

Examples

Monthly House Rental

Monthly Village Dues

Annual Building Permit

Penalty

Fields

- id

- payment_name

- description

---

## 5.11 Payments

Purpose

Stores payment transactions.

Fields

- id

- household_id

- payment_type_id

- amount

- penalty

- payment_date

- due_date

- status

- remarks

Status

Paid

Pending

Overdue

---

## 5.12 Penalties

Purpose

Stores penalties.

Fields

- id

- payment_id

- amount

- reason

- imposed_date

---

## 5.13 Announcements

Purpose

Stores announcements.

Fields

- id

- village_id

- user_id

- title

- description

- attachment

- announcement_date

---

## 5.14 Announcement Responses

Purpose

Stores resident responses.

Fields

- id

- announcement_id

- resident_id

- response

Responses

Attending

Not Attending

Maybe

---

## 5.15 Participation Records

Purpose

Stores participation verification.

Workflow

Resident

↓

Pending

↓

Secretary Verification

↓

Approved

Fields

- id

- announcement_id

- resident_id

- secretary_id

- status

- remarks

- verified_at

---

## 5.16 Prediction Results

Purpose

Stores AI prediction results.

Fields

- id

- household_id

- prediction_type

- prediction_result

- confidence_score

- generated_at

Examples

Good Payer

Delinquent

Population Growth

Participation Trend

---

## 5.17 Audit Logs

Purpose

Stores security logs.

Fields

- id

- user_id

- action

- module

- ip_address

- created_at

Examples

Login

Logout

Create

Delete

Update

Approval

---

## 5.18 Activity Logs

Purpose

Stores dashboard activities.

Fields

- id

- user_id

- activity

- module

- created_at

---

# 6. Database Relationships

Village

↓

hasMany Households

Village

↓

hasMany Users

Village

↓

hasMany Announcements

Village

↓

hasMany Officers

Household

↓

hasMany Residents

Household

↓

hasMany Payments

Household

↓

hasMany Predictions

Resident

↓

belongsTo Household

Resident

↓

hasMany Participation Records

Resident

↓

hasMany Announcement Responses

User

↓

belongsTo Role

Role

↓

hasMany Users

---

# 7. GIS Integration

Each household stores:

- Latitude

- Longitude

These coordinates are used by Leaflet.js to display household locations on the interactive map.

Each marker displays:

- Household Number

- Household Head

- Payment Classification

- Participation Status

- Household Information

---

# 8. Predictive Analytics Integration

Prediction results are stored separately from payment records.

This allows future retraining of prediction models without modifying historical payment transactions.

Prediction categories include:

- Payment Behavior

- Population Trend

- Participation Trend

---

# 9. Database Index Recommendations

Recommended indexes

users.username

households.household_number

residents.resident_code

payments.payment_date

payments.status

participation_records.status

prediction_results.household_id

These indexes improve search performance and dashboard loading.

---

# 10. Naming Conventions

Tables

Plural

Examples

users

payments

households

Models

Singular

User

Resident

Household

Village

Controllers

Singular

ResidentController

VillageController

PaymentController

---

# 11. Summary

This schema provides a scalable, normalized, and secure database structure capable of supporting centralized multi-village management, GIS visualization, role-based workflow, payment monitoring, participation tracking, data analytics, and predictive analytics.

Future enhancements can be implemented without major database redesign due to the modular architecture of the schema.

---

End of Document