# User Roles and Permissions

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

**Version:** 1.0

---

# 1. Purpose

This document defines the user roles, permissions, responsibilities, and access restrictions implemented in the proposed system.

The system implements a **Role-Based Access Control (RBAC)** model with a hierarchical workflow to ensure that every user can only perform tasks assigned to their role.

The authorization mechanism shall be enforced through Laravel Middleware and Policy classes.

---

# 2. Role Hierarchy

The system follows the hierarchical structure below.

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

Only higher-level roles can create and manage lower-level roles.

---

# 3. User Roles

The system consists of five (5) user roles.

| Role | Description |

|------|-------------|

| Super Admin | City Planning and Development Office (CPDO) |

| Admin | Village President |

| Secretary | Village Secretary |

| Treasurer | Village Treasurer |

| Resident | Registered Village Resident |

---

# 4. Hierarchical Account Management

The system does **not** provide public registration.

Account creation follows a strict hierarchy.

## Super Admin

Can create:

- Admin Accounts

Cannot create:

- Secretary

- Treasurer

- Resident

directly.

The Super Admin first assigns an Admin to each village.

---

## Admin

Can create:

- Secretary

- Treasurer

- Resident

Only inside his/her assigned village.

Cannot manage users from another village.

---

## Secretary

Cannot create accounts.

---

## Treasurer

Cannot create accounts.

---

## Resident

Cannot create accounts.

---

# 5. Permission Matrix

Legend

✔ Allowed

✖ Not Allowed

| Module | Super Admin | Admin | Secretary | Treasurer | Resident |

|---------|-------------|--------|------------|------------|-----------|

| Login | ✔ | ✔ | ✔ | ✔ | ✔ |

| Logout | ✔ | ✔ | ✔ | ✔ | ✔ |

| Edit Own Profile | ✔ | ✔ | ✔ | ✔ | ✔ |

| Change Password | ✔ | ✔ | ✔ | ✔ | ✔ |

| View Dashboard | ✔ | ✔ | ✔ | ✔ | ✔ |

| View GIS | ✔ | ✔ | ✔ | ✔ | ✔ |

| View Household GIS | ✔ | ✔ | ✔ | ✔ | ✔ |

| Manage Villages | ✔ | ✖ | ✖ | ✖ | ✖ |

| Manage Village Profile | ✔ | ✔ | ✔ | ✖ | ✖ |

| Manage Organizational Structure | ✔ | ✔ | ✔ | ✖ | ✖ |

| Upload Officer Photos | ✔ | ✔ | ✔ | ✖ | ✖ |

| Upload Village Documents | ✔ | ✔ | ✔ | ✖ | ✖ |

| Manage Households | ✔ | ✔ | ✔ | ✖ | ✖ |

| Manage Residents | ✔ | ✔ | ✔ | ✖ | ✖ |

| Approve Resident Records | ✔ | ✔ | ✖ | ✖ | ✖ |

| Manage Payments | ✔ (View/Monitor) | ✖* | ✖ | ✔* | ✖ |

| Manage Penalties | ✔ (La Union only) | ✖ | ✖ | ✔ (Comagascas) | ✖ |

| View Payment History | ✔ | ✔ | ✔ | ✔ | ✔ |

| Manage Announcements | ✔ | ✔ | ✔ | ✖ | ✖ |

| Verify Participation | ✔ | ✔ | ✔ | ✖ | ✖ |

| Confirm Participation | ✖ | ✖ | ✖ | ✖ | ✔ |

| View Analytics | ✔ | ✔ | ✔ | ✔ | ✖ |

| View Predictive Analytics | ✔ | ✔ | ✖ | ✔ | ✔ (Own Household Only) |

| Generate Reports | ✔ | ✔ | ✔ | ✔ | ✖ |

| Manage User Accounts | ✔ | ✔ | ✖ | ✖ | ✖ |

| View Audit Logs | ✔ | ✖ | ✖ | ✖ | ✖ |

| Manage System Settings | ✔ | ✖ | ✖ | ✖ | ✖ |

\* Payment management depends on village configuration:

- **Comagascas:** Treasurer manages payments.

- **La Union (Angel Village):** Super Admin (CPDO) manages payment records and penalties.

- **Calibunan:** To be configured after requirements gathering.

---

# 6. Responsibilities per Role

## 6.1 Super Admin (CPDO)

The Super Admin is responsible for managing the entire system.

### Responsibilities

- Manage all villages

- Create Admin accounts

- Monitor all villages

- View GIS of all villages

- Monitor payment analytics

- View predictive analytics

- Generate city-wide reports

- Configure villages

- Monitor participation

- Manage system settings

- View audit logs

---

## 6.2 Admin (Village President)

The Admin is responsible for managing one assigned village.

### Responsibilities

- Manage resident records

- Manage household records

- Approve resident information

- Create Secretary accounts

- Create Treasurer accounts

- Create Resident accounts

- Manage announcements

- View village GIS

- View analytics

- Monitor participation

- Generate village reports

---

## 6.3 Secretary

The Secretary is responsible for maintaining administrative records.

### Responsibilities

- Update village profile

- Update village information

- Manage village documents

- Upload officer photographs

- Update organizational structure

- Manage resident records

- Verify participation records

- Manage announcements

---

## 6.4 Treasurer

The Treasurer manages village financial records.

### Responsibilities

- Record payments (Comagascas)

- Manage monthly dues

- Manage monthly rentals

- Generate collection reports

- View payment history

- Monitor payment statistics

---

## 6.5 Resident

Residents have limited access.

### Responsibilities

- View household information

- View village information

- View GIS location

- Respond to announcements

- Confirm participation

- View payment records

- View household payment prediction

- Update limited profile

---

# 7. Sidebar Navigation by Role

## Super Admin

- Dashboard

- Villages

- GIS Map

- Households

- Residents

- Analytics

- Predictive Analytics

- Reports

- User Management

- Audit Logs

- Settings

- Profile

---

## Admin

- Dashboard

- Residents

- Households

- GIS Map

- Announcements

- Participation

- Analytics

- Reports

- User Management

- Profile

---

## Secretary

- Dashboard

- Village Profile

- Organizational Structure

- Village Documents

- Residents

- GIS Map

- Announcements

- Participation Verification

- Reports

- Profile

---

## Treasurer

- Dashboard

- Payments

- Penalties

- GIS Map

- Payment Reports

- Analytics

- Profile

---

## Resident

- Dashboard

- Household

- GIS Map

- Announcements

- Participation

- Payment Status

- Profile

---

# 8. Middleware Authorization

Every protected route shall use Laravel Middleware.

Examples

```

auth

role:super-admin

role:admin

role:secretary

role:treasurer

role:resident

```

Unauthorized users shall receive:

**403 Forbidden**

No user shall bypass authorization by manually entering URLs.

---

# 9. Role-Based Workflow

The system follows a hierarchical workflow.

```

Super Admin

↓

Assigns Admin

↓

Admin

↓

Creates Secretary

↓

Creates Treasurer

↓

Creates Resident

↓

Residents interact with the system

↓

Secretary verifies participation

↓

Treasurer updates payments

↓

Admin monitors village

↓

Super Admin monitors all villages

```

---

# 10. Security Policies

The RBAC implementation shall comply with the following principles.

- Least Privilege Principle

- Separation of Duties

- Authentication Required

- Authorization Required

- Audit Logging

- Secure Password Storage

- Session Management

- Route Protection

---

# 11. Future Expansion

The RBAC architecture supports additional roles in future versions, including:

- CPDO Staff

- GIS Administrator

- Financial Officer

- Auditor

- Mobile User

without requiring major redesign.

---

# 12. Summary

The Role-Based Access Control (RBAC) model ensures that every user accesses only the information and functions appropriate to their responsibilities. Combined with the hierarchical account management workflow, it provides a secure, organized, and scalable authorization mechanism suitable for a centralized multi-village management system deployed under the City Planning and Development Office (CPDO).

---

**End of Document**