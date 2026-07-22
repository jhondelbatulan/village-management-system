# GIS Module Specification

## Web-Based GIS-Enabled Multi-Village Management System with Role-Based Workflow and Predictive Analytics

**Version:** 1.0

---

# 1. Purpose

This document defines the Geographic Information System (GIS) module of the proposed Web-Based GIS-Enabled Multi-Village Management System.

The GIS module provides an interactive map visualization that enables authorized users to view villages, households, and geographical information. It supports spatial visualization of household locations while integrating resident information, payment classifications, participation monitoring, and predictive analytics.

The GIS module shall be developed using **Leaflet.js** with **OpenStreetMap** as the base map provider.

---

# 2. Objectives

The GIS Module aims to:

- Visualize villages using an interactive map.

- Display household locations.

- Display household information through map markers.

- Improve monitoring of village households.

- Support planning and decision-making.

- Integrate household information with GIS visualization.

- Support predictive analytics visualization.

---

# 3. GIS Technology Stack

Frontend

- Leaflet.js

- OpenStreetMap

- JavaScript

- Bootstrap 5

Backend

- Laravel 12

- PHP

Database

- MySQL

---

# 4. GIS Components

The GIS module consists of the following components.

## Village Map

Displays every registered village.

Initially:

- Comagascas

- La Union (Angel Village)

- Calibunan

Future villages may be added without modifying the GIS architecture.

---

## Household Map

Displays every household using markers.

Each marker represents one household.

The household marker stores:

- Latitude

- Longitude

---

## Marker Popup

Clicking a household marker opens an information popup.

The popup displays:

- Household Number

- Household Head

- Village

- Purok

- Address

- Number of Residents

- Payment Classification

- Participation Status

If authorized:

- Household Members

- Household Information

---

# 5. Household Location Registration

When registering a household:

Secretary or Admin shall:

1. Open GIS Map

2. Search household location

3. Click exact household location

4. Save coordinates

The system stores:

Latitude

Longitude

Household coordinates cannot be empty.

---

# 6. Village Profile GIS

Each village shall contain:

Village Name

Village Description

Village Logo

Date Established

Total Households

Total Population

Village Boundary (Future)

Officer Information

---

# 7. GIS Search

Users shall search by:

Village

Household Number

Household Head

Resident Name

Address

Search results automatically center the map.

---

# 8. GIS Filtering

The GIS shall support filtering.

Examples:

Filter by Village

Filter by Payment Status

Filter by Household Classification

Filter by Participation

Filter by Prediction

---

# 9. GIS Marker Colors

Different marker colors represent household status.

Green

Good Payer

Blue

Average Payer

Orange

Pending Payment

Red

Delinquent Household

Gray

Inactive Household

Marker colors shall update automatically whenever payment status changes.

---

# 10. GIS Information by User Role

## Super Admin

Can view:

- All Villages

- All Households

- Household Information

- Payment Classification

- Participation Status

- Predictive Analytics

- Village Statistics

---

## Admin

Can view:

Only assigned village.

Can:

- View households

- Register household locations

- Update household coordinates

- View household information

- View analytics

---

## Secretary

Can:

- Register household coordinates

- Update coordinates

- View household information

- View village profile

Cannot modify predictive analytics.

---

## Treasurer

Can:

- View household locations

- View payment classifications

- View payment history

Cannot update household coordinates.

---

## Resident

Residents may view:

- Their own household location

- Village map

- Nearby village facilities (future)

Residents cannot modify GIS information.

---

# 11. Household Popup Information

The popup contains.

----------------------------------

Household Number

Household Head

Village

Address

Residents

Payment Classification

Participation Rating

Prediction Label

Button

View Household Profile

----------------------------------

---

# 12. GIS Dashboard Widgets

The GIS dashboard includes.

Village Summary

Total Villages

Total Households

Total Residents

Map Legend

Search Bar

Filter Panel

Analytics Summary

---

# 13. GIS Integration with Analytics

The GIS module integrates with analytics.

Selecting a village automatically updates:

Population

Gender

Age

Payments

Participation

Predictions

without refreshing the page.

---

# 14. GIS Integration with Predictive Analytics

Each household marker displays prediction labels.

Examples:

Good Payer

Average Payer

High Risk

Delinquent

Hovering the marker displays the prediction.

---

# 15. GIS Database Fields

Villages Table

latitude

longitude

Households Table

latitude

longitude

prediction_label

payment_status

participation_status

---

# 16. Future GIS Enhancements

Future versions may include:

Village Boundary Polygon

Road Network

Route Navigation

Disaster Risk Mapping

Flood Zones

Satellite Map

Heat Maps

Mobile GPS Tracking

Offline GIS

Google Maps Integration

---

# 17. GIS User Interface

The GIS page shall contain:

------------------------------------------------

Header

Search Bar

Filter Panel

Interactive Map

Legend

Village Statistics

Household Statistics

------------------------------------------------

The layout shall be responsive and compatible with desktop, tablet, and mobile devices.

---

# 18. GIS Workflow

Secretary/Admin

↓

Register Household

↓

Assign GPS Coordinates

↓

Save Latitude & Longitude

↓

Leaflet.js Displays Marker

↓

Users View Household Information

↓

Analytics Updated

↓

Prediction Updated

---

# 19. Summary

The GIS Module serves as the geographical visualization component of the system. By integrating household records with spatial information, the system provides an interactive platform for monitoring villages, locating households, supporting planning activities, and enhancing decision-making through GIS visualization and predictive analytics.

The module is designed to be scalable, allowing additional villages, household categories, and GIS features to be incorporated without major architectural modifications.

---

End of Document