# System Requirements Specification (SRS)

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

**Version:** 1.0  

**Framework:** Laravel 12  

**Database:** MySQL  

**Development Methodology:** Agile Software Development Methodology

---

# 1. Introduction

This document defines the complete functional and non-functional requirements of the Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics.

The purpose of this document is to establish a comprehensive reference for developers, researchers, and future maintainers of the system. It serves as the primary specification that guides the design, implementation, testing, deployment, and maintenance of the proposed system.

---

# 2. System Scope

The proposed system is intended to serve as a centralized web-based platform for managing multiple resettlement villages under the City Planning and Development Office (CPDO) of Cabadbaran City.

The system provides centralized management of villages, residents, households, GIS visualization, announcements, participation monitoring, payment monitoring, reports, and predictive analytics while enforcing strict Role-Based Access Control (RBAC).

Initially, the system will support the following villages:

- Comagascas Resettlement Village

- La Union (Angel Village)

- Calibunan Resettlement Village

The architecture is designed to accommodate future expansion to additional villages without requiring major modifications.

---

# 3. Functional Requirements

The system shall provide the following functional requirements.

---

## FR-01 Authentication

The system shall:

- Allow users to log in using their assigned username and password.

- Encrypt passwords before storing them in the database.

- Allow users to securely log out.

- Support password reset functionality.

- Automatically terminate inactive sessions.

- Record login and logout activities.

---

## FR-02 User Profile Management

The system shall allow users to:

- View their profile.

- Update personal information.

- Change password.

- Upload or replace profile picture.

---

## FR-03 Role-Based Access Control (RBAC)

The system shall implement strict Role-Based Access Control.

Supported roles:

- Super Admin

- Admin

- Secretary

- Treasurer

- Resident

Each role shall only access modules and functions authorized for that role.

Unauthorized access shall return a **403 Unauthorized** response.

---

## FR-04 Hierarchical Account Management

The system shall implement hierarchical account creation.

Super Admin shall:

- Create Admin accounts.

Admin shall:

- Create Secretary accounts.

- Create Treasurer accounts.

- Create Resident accounts.

Secretary, Treasurer, and Resident users shall not be permitted to create accounts.

Each account shall be assigned to only one village.

---

## FR-05 Village Management

The system shall allow authorized users to:

- Create village profiles.

- Update village information.

- Manage village descriptions.

- Upload village documents.

- Manage organizational structure.

- Upload officer photographs.

---

## FR-06 Resident Management

The system shall allow authorized users to:

- Register residents.

- Update resident records.

- Archive resident records.

- Search residents.

- Import resident information.

- Export resident information.

---

## FR-07 Household Management

The system shall allow users to:

- Register households.

- Assign residents to households.

- Transfer residents.

- Update household information.

- View household members.

---

## FR-08 GIS Module

The system shall integrate Geographic Information System (GIS) capabilities using Leaflet.js.

The GIS module shall:

- Display village boundaries.

- Display household locations.

- Display household information.

- Display village information.

- Support zooming.

- Support map navigation.

- Support household search.

- Support village filtering.

Each household marker shall display:

- Household Head

- Address

- Household Number

- Household Information

- Payment Classification

- Participation Status

---

## FR-09 Payment Management

The system shall support configurable payment management.

### Comagascas

- Monthly House Rental

- Monthly Village Dues

### La Union (Angel Village)

- Annual Building Permit

- Penalty Monitoring

The system shall:

- Record payments.

- Update payment status.

- Generate payment reports.

- Record penalties.

- Display payment history.

---

## FR-10 Announcement Management

Authorized users shall:

- Publish announcements.

- Edit announcements.

- Delete announcements.

- Attach documents.

- Notify residents.

Residents shall:

- View announcements.

- Confirm participation.

---

## FR-11 Participation Monitoring

Residents shall:

- Confirm attendance.

Status:

Pending

↓

Secretary Verification

↓

Approved

↓

Attendance Recorded

Super Admin and Admin shall monitor participation statistics.

---

## FR-12 Reports

The system shall generate reports including:

- Resident Reports

- Household Reports

- Payment Reports

- Participation Reports

- Village Reports

- Analytics Reports

Reports shall support:

- PDF Export

- Excel Export

- Print Preview

---

## FR-13 Dashboard

Each user shall have a customized dashboard displaying:

- Summary statistics

- Pending tasks

- Notifications

- Recent activities

- Analytics

- GIS summary

---

## FR-14 Data Analytics

The system shall provide dashboards including:

Population

Gender

Age

Civil Status

Occupation

Educational Attainment

Income Distribution

Household Statistics

Village Comparison

Payment Statistics

Participation Statistics

Interactive charts shall be implemented using Chart.js.

---

## FR-15 Predictive Analytics

The system shall analyze historical data to predict:

- Good Payer

- Average Payer

- Delinquent Household

- Expected Collection

- Participation Trend

- Population Growth Trend

Predictions shall be generated using Python and displayed within the Laravel application.

---

# 4. Non-Functional Requirements

---

## Performance

The system shall:

- Load dashboard pages within 3 seconds under normal conditions.

- Support concurrent users.

- Efficiently process large resident datasets.

---

## Reliability

The system shall:

- Prevent unauthorized access.

- Protect against data loss.

- Maintain transaction consistency.

---

## Security

The system shall implement:

- Password Hashing

- CSRF Protection

- XSS Protection

- SQL Injection Prevention

- Middleware Authorization

- Role-Based Access Control

- Audit Logs

---

## Availability

The system shall be accessible through modern web browsers with an active internet connection once deployed.

---

## Maintainability

The project shall follow:

- MVC Architecture

- Modular Design

- Clean Code Principles

- Reusable Components

- Repository Pattern (optional)

- Service Layer Architecture where appropriate

---

## Scalability

The system shall support:

- Additional villages

- Additional payment types

- Additional user roles (if needed)

- Additional analytics modules

without major redesign.

---

## Responsiveness

The user interface shall be fully responsive and support:

- Desktop

- Laptop

- Tablet

- Mobile Phone

Bootstrap 5 shall be used for responsive layouts.

---

# 5. Hardware Requirements

## Development Computer

| Component | Minimum | Recommended |

|------------|----------|--------------|

| Processor | Intel Core i3 | Intel Core i5/i7 or Ryzen 5 |

| RAM | 8 GB | 16 GB |

| Storage | 256 GB SSD | 512 GB SSD |

| Internet | Required | Stable Broadband |

---

## Production Server

| Component | Recommended |

|------------|--------------|

| CPU | Quad Core |

| RAM | 8 GB |

| Storage | SSD |

| Database | MySQL |

| Operating System | Linux Server |

---

# 6. Software Requirements

| Software | Version |

|------------|---------|

| PHP | 8.2+ |

| Laravel | 12 |

| MySQL | 8.x |

| XAMPP | Latest Stable |

| Composer | Latest |

| Node.js | Latest LTS |

| Bootstrap | 5 |

| Leaflet.js | Latest |

| Chart.js | Latest |

| Python | 3.11+ |

| Visual Studio Code | Latest |

| Git | Latest |

---

# 7. Browser Compatibility

The system shall support:

- Google Chrome

- Microsoft Edge

- Mozilla Firefox

- Opera

---

# 8. Deployment Requirements

The system shall support deployment on:

- [Localhost](http://Localhost) (XAMPP)

- Apache Web Server

- cPanel Shared Hosting

- VPS Linux Server

The deployment shall include:

- MySQL Database

- Laravel Application

- Python Runtime for Predictive Analytics

- Storage Linking

- Scheduled Tasks (if required)

---

# 9. Assumptions and Constraints

- Each resident belongs to only one household.

- Each household belongs to one village.

- Each village has one assigned Admin.

- Super Admin manages all villages.

- GIS coordinates must be manually assigned during household registration.

- Predictive Analytics depends on sufficient historical data.

- Internet connectivity is required after deployment.

---

# 10. Summary

This document defines the complete functional and non-functional requirements of the proposed system. It serves as the foundation for system architecture, database design, implementation, testing, and deployment. All future development phases shall adhere to the specifications outlined in this document to ensure consistency, maintainability, and scalability.

---

**End of Document**