# Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

**Version:** 1.0  

**Project Type:** Undergraduate BSIT Capstone Project  

**Framework:** Laravel 12  

**Development Methodology:** Agile Software Development Methodology  

**Prepared By:**  

- Jhondel Batulan

- Dale Adrian B. Auguis

- Dennis P. Casuco Jr.

---

# 1. Project Overview

The Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics is a centralized web application designed to improve the management of resettlement villages under the City Planning and Development Office (CPDO) of Cabadbaran City.

The system aims to replace manual and fragmented record-keeping processes with a centralized digital platform capable of managing resident records, household information, village profiles, organizational structures, payment monitoring, announcements, participation records, GIS visualization, and predictive analytics.

Unlike conventional village management systems, this project integrates Geographic Information System (GIS) technology and Data Analytics to support evidence-based decision-making while implementing a strict Role-Based Workflow to ensure secure and organized management of information.

---

# 2. Vision

To provide the City Planning and Development Office (CPDO) and participating villages with a modern, centralized, secure, and intelligent management system that enhances administrative efficiency, transparency, and decision-making through GIS and predictive analytics.

---

# 3. Mission

The system aims to:

- Centralize resident and household information.

- Improve data accuracy through role-based workflow.

- Digitize village administrative processes.

- Provide GIS-based visualization of villages and households.

- Generate meaningful statistical reports and dashboards.

- Support data-driven planning through predictive analytics.

- Improve communication between village officials and residents.

---

# 4. Target Users

The system serves five primary user roles.

## Super Admin (CPDO)

The Super Admin is the highest-level user of the system.

Responsibilities include:

- Manage all villages

- Create Admin accounts

- View all GIS information

- View all analytics dashboards

- View predictive analytics

- Manage system configuration

- Monitor all village activities

- Generate city-wide reports

---

## Admin (Village President)

Each village has one designated Admin.

Responsibilities include:

- Manage residents

- Manage households

- Approve resident records

- Create Secretary accounts

- Create Treasurer accounts

- Create Resident accounts

- Manage announcements

- View village GIS

- View village analytics

---

## Secretary

Responsibilities include:

- Manage village profile

- Update village information

- Manage organizational structure

- Upload officer photos

- Upload village documents

- Manage resident records

- Verify participation records

- Manage announcements

---

## Treasurer

Responsibilities include:

- Manage payment records

- Update payment status

- Generate payment reports

- Manage penalties

- Monitor collections

---

## Resident

Responsibilities include:

- View household profile

- View village information

- Respond to announcements

- Submit participation confirmation

- View payment records

- Update limited personal profile

---

# 5. Supported Villages

Initially, the system supports the following villages.

## Comagascas

Features include:

- Monthly House Rental

- Monthly Village Dues

- Household Monitoring

- GIS Household Mapping

---

## La Union (Angel Village)

Features include:

- Annual Building Permit Monitoring

- Variable Permit Rates

- Penalty Management

- Household Monitoring

- GIS Household Mapping

---

## Calibunan

(Currently under further survey)

The system architecture allows future configuration once official requirements become available.

---

# 6. Major Modules

The system consists of the following modules.

### Authentication

- Login

- Logout

- Password Management

- Profile Management

---

### Role-Based Access Control (RBAC)

- Five user roles

- Strict permission enforcement

- Middleware protection

- Dynamic navigation

---

### Village Management

- Village Information

- Village Profile

- Organizational Structure

- Officer Management

---

### Resident Management

- Resident Registration

- Household Management

- Family Members

- Approval Workflow

---

### GIS Module

- Interactive Village Map

- Household Locations

- Village Information

- Household Information

- Search & Filter

- OpenStreetMap Integration

- Leaflet.js

---

### Payment Management

Supports village-specific payment configurations.

Examples:

Comagascas

- Monthly House Rental

- Monthly Dues

La Union

- Annual Building Permit

- Penalty Monitoring

---

### Participation Monitoring

Residents confirm attendance.

Secretary verifies attendance.

Super Admin and Admin monitor household participation.

---

### Announcement Module

Village announcements

Meeting notices

Emergency notices

Community activities

Resident responses

---

### Data Analytics

Provides dashboards including:

- Population Statistics

- Household Statistics

- Gender Distribution

- Age Distribution

- Civil Status

- Educational Attainment

- Occupation Distribution

- Income Distribution

- Village Comparison

- Payment Statistics

- Participation Statistics

---

### Predictive Analytics

Predictive features include:

- Household Payment Behavior

- Expected Collection

- Population Growth Trend

- Participation Trend

Predictions are generated using historical data collected by the system.

---

### Reports

Generate reports in:

- PDF

- Excel

Including:

- Resident Reports

- Household Reports

- Payment Reports

- Participation Reports

- Analytics Reports

---

# 7. Technology Stack

Backend

- Laravel 12

- PHP 8.2

Database

- MySQL

Development Environment

- XAMPP

Frontend

- HTML5

- CSS3

- Bootstrap 5

- JavaScript

GIS

- Leaflet.js

- OpenStreetMap

Charts

- Chart.js

Predictive Analytics

- Python

- Scikit-learn

- Pandas

Version Control

- Git

- GitHub

---

# 8. Project Objectives

The project aims to:

- Centralize village management.

- Implement secure Role-Based Workflow.

- Improve resident data management.

- Integrate GIS visualization.

- Provide comprehensive data analytics.

- Support decision-making through predictive analytics.

---

# 9. Development Philosophy

The system shall be developed using the following principles:

- Clean Architecture

- MVC Design Pattern

- Reusable Components

- Modular Development

- Responsive User Interface

- Secure Authentication

- Strict Role-Based Authorization

- Scalability

- Maintainability

- Readable Source Code

---

# 10. Project Status

Current Development Stage:

✔ Project Proposal Approved

✔ Major Revisions Incorporated

✔ Laravel Project Initialized

✔ Development Documentation Started

Next Milestone:

Phase 2 — Authentication, RBAC, and Database Development.

---

# 11. Long-Term Goal

The system is designed to be scalable and may support additional villages, new government services, mobile applications, cloud deployment, and advanced AI-assisted analytics in future versions.

---

**End of Document**