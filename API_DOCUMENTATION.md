# Procurement System API Documentation

**Version:** 1.0.0  
**Base URL:** `http://127.0.0.1:3000/api`  
**Authentication:** Session-based (cookies)

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Error Handling](#error-handling)
5. [Health Check Endpoints](#health-check-endpoints)
6. [Requestor API Endpoints](#requestor-api-endpoints)
7. [Rate Limiting](#rate-limiting)
8. [Examples](#examples)

---

## Overview

The Procurement System API provides RESTful endpoints for managing purchase orders, suppliers, and system monitoring. All API responses follow a standardized JSON format for consistency.

### Base URL
```
Production: http://127.0.0.1:3000/api
Development: http://127.0.0.1:3000/api
```

### Content Type
All requests and responses use `application/json` content type.

---

## Authentication

### Session-Based Authentication

The API uses session-based authentication. Users must be logged in via the web interface before making API requests.

**Required Headers:**
```
Cookie: laravel_session=<session_token>
X-CSRF-TOKEN: <csrf_token>
```

### Authentication Errors

**401 Unauthorized**
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHENTICATED",
    "message": "Authentication required",
    "status": 401
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

**403 Forbidden**
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "Insufficient permissions",
    "status": 403
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

## Response Format

### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

### Error Response

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message",
    "status": 400,
    "details": {
      // Optional error details
    }
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "items": [
      // Array of items
    ],
    "pagination": {
      "total": 100,
      "per_page": 15,
      "current_page": 1,
      "last_page": 7,
      "from": 1,
      "to": 15
    }
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

## Error Handling

### Error Codes

| Code | Description | HTTP Status |
|------|-------------|-------------|
| `UNAUTHENTICATED` | User not authenticated | 401 |
| `FORBIDDEN` | Insufficient permissions | 403 |
| `NOT_FOUND` | Resource not found | 404 |
| `VALIDATION_ERROR` | Request validation failed | 422 |
| `SERVER_ERROR` | Internal server error | 500 |
| `METRICS_ERROR` | Failed to retrieve metrics | 500 |
| `PO_FETCH_ERROR` | Failed to fetch purchase orders | 500 |

### Validation Errors

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "status": 422,
    "details": {
      "field_name": [
        "Error message 1",
        "Error message 2"
      ]
    }
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

## Health Check Endpoints

### Basic Health Check

**Endpoint:** `GET /api/health`

**Description:** Basic health check to verify service is running.

**Authentication:** Not required

**Response:**
```json
{
  "success": true,
  "message": "Service is healthy",
  "data": {
    "status": "healthy",
    "timestamp": "2025-10-29T19:00:00Z",
    "uptime": "5d 3h 45m"
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

### Detailed Health Check

**Endpoint:** `GET /api/health/detailed`

**Description:** Detailed health check including database, cache, storage, and memory status.

**Authentication:** Not required

**Response:**
```json
{
  "success": true,
  "status": "healthy",
  "checks": {
    "database": {
      "status": "healthy",
      "response_time_ms": 15.23,
      "message": "Database connection successful"
    },
    "cache": {
      "status": "healthy",
      "response_time_ms": 5.12,
      "message": "Cache working properly"
    },
    "storage": {
      "status": "healthy",
      "message": "Storage is accessible",
      "writable": true,
      "readable": true
    },
    "memory": {
      "status": "healthy",
      "usage_bytes": 45678912,
      "usage_mb": 43.56,
      "limit": "256M",
      "usage_percent": 17.03,
      "message": "Memory usage at 17.03%"
    }
  },
  "timestamp": "2025-10-29T19:00:00Z",
  "uptime": "5d 3h 45m",
  "version": "1.0.0"
}
```

**Status Values:**
- `healthy`: All systems operational
- `degraded`: Some systems experiencing issues
- `unhealthy`: Critical systems down

---

## Requestor API Endpoints

### Get Dashboard Metrics

**Endpoint:** `GET /api/requestor/metrics`

**Description:** Retrieve dashboard metrics for the authenticated requestor.

**Authentication:** Required (requestor role)

**Response:**
```json
{
  "success": true,
  "message": "Metrics retrieved successfully",
  "data": {
    "total_pos": 150,
    "verified_pos": 45,
    "approved_pos": 78,
    "pending_pos": 12,
    "total_value": 1250000.50,
    "average_value": 8333.34
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

**Cache:** Results cached for 5 minutes

---

### Get Recent Purchase Orders

**Endpoint:** `GET /api/requestor/purchase-orders/recent`

**Description:** Retrieve recent purchase orders for the authenticated requestor.

**Authentication:** Required (requestor role)

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `limit` | integer | 5 | Number of records (1-50) |

**Example Request:**
```
GET /api/requestor/purchase-orders/recent?limit=10
```

**Response:**
```json
{
  "success": true,
  "message": "Recent purchase orders retrieved successfully",
  "data": [
    {
      "purchase_order_id": "uuid-123",
      "purchase_order_no": "20251029-001",
      "purpose": "Office Supplies",
      "total": 15000.00,
      "created_at": "2025-10-29T10:00:00Z",
      "date_requested": "2025-10-29",
      "status_name": "Pending",
      "supplier_name": "ABC Supplies Inc."
    }
  ],
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

### Get Purchase Orders (Paginated)

**Endpoint:** `GET /api/requestor/purchase-orders`

**Description:** Retrieve paginated list of purchase orders with search and filter capabilities.

**Authentication:** Required (requestor role)

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items per page |
| `page` | integer | 1 | Page number |
| `search` | string | - | Search by PO number, purpose, or supplier |
| `status` | string | - | Filter by status name |

**Example Request:**
```
GET /api/requestor/purchase-orders?per_page=20&page=2&search=office&status=Approved
```

**Response:**
```json
{
  "success": true,
  "message": "Purchase orders retrieved successfully",
  "data": {
    "items": [
      {
        "purchase_order_id": "uuid-123",
        "purchase_order_no": "20251029-001",
        "purpose": "Office Supplies",
        "total": 15000.00,
        "subtotal": 14000.00,
        "shipping_fee": 1000.00,
        "discount": 0.00,
        "created_at": "2025-10-29T10:00:00Z",
        "date_requested": "2025-10-29",
        "delivery_date": "2025-11-05",
        "status_name": "Approved",
        "status_id": "status-uuid",
        "supplier_name": "ABC Supplies Inc.",
        "supplier_id": "supplier-uuid"
      }
    ],
    "pagination": {
      "total": 150,
      "per_page": 20,
      "current_page": 2,
      "last_page": 8,
      "from": 21,
      "to": 40
    }
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

### Get Purchase Order Details

**Endpoint:** `GET /api/requestor/purchase-orders/{poNo}`

**Description:** Retrieve detailed information for a specific purchase order.

**Authentication:** Required (requestor role)

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `poNo` | string | Purchase order number |

**Example Request:**
```
GET /api/requestor/purchase-orders/20251029-001
```

**Response:**
```json
{
  "success": true,
  "message": "Purchase order retrieved successfully",
  "data": {
    "po": {
      "purchase_order_id": "uuid-123",
      "purchase_order_no": "20251029-001",
      "requestor_id": "user-uuid",
      "supplier_id": "supplier-uuid",
      "purpose": "Office Supplies",
      "official_receipt_no": "OR-2025-001",
      "date_requested": "2025-10-29",
      "delivery_date": "2025-11-05",
      "shipping_fee": 1000.00,
      "discount": 0.00,
      "subtotal": 14000.00,
      "total": 15000.00,
      "created_at": "2025-10-29T10:00:00Z",
      "updated_at": "2025-10-29T10:00:00Z",
      "status_name": "Approved",
      "status_id": "status-uuid",
      "supplier_name": "ABC Supplies Inc.",
      "supplier_address": "123 Main St, City",
      "contact_person": "John Doe",
      "contact_number": "+63 912 345 6789",
      "tin_no": "123-456-789-000",
      "vat_type": "VAT"
    },
    "items": [
      {
        "item_id": "item-uuid",
        "item_name": "Ballpen",
        "item_description": "Blue ballpen, 0.7mm",
        "quantity": 100,
        "unit_price": 10.00,
        "total_cost": 1000.00
      }
    ],
    "approvals": [
      {
        "status_name": "Approved",
        "prepared_at": "2025-10-29T11:00:00Z",
        "remarks": "Approved for procurement",
        "prepared_by": "Admin User"
      },
      {
        "status_name": "Pending",
        "prepared_at": "2025-10-29T10:00:00Z",
        "remarks": "Initial submission",
        "prepared_by": "Requestor User"
      }
    ]
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

### Get Statistics

**Endpoint:** `GET /api/requestor/statistics`

**Description:** Retrieve statistical data for dashboard visualizations.

**Authentication:** Required (requestor role)

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `date_from` | date | 1 month ago | Start date (YYYY-MM-DD) |
| `date_to` | date | today | End date (YYYY-MM-DD) |

**Example Request:**
```
GET /api/requestor/statistics?date_from=2025-10-01&date_to=2025-10-31
```

**Response:**
```json
{
  "success": true,
  "message": "Statistics retrieved successfully",
  "data": {
    "period": {
      "from": "2025-10-01",
      "to": "2025-10-31"
    },
    "total_pos": 45,
    "total_value": 650000.00,
    "by_status": [
      {
        "status_name": "Approved",
        "count": 20
      },
      {
        "status_name": "Pending",
        "count": 15
      },
      {
        "status_name": "Verified",
        "count": 10
      }
    ],
    "by_month": [
      {
        "year": 2025,
        "month": 10,
        "count": 45,
        "total_value": 650000.00
      }
    ]
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

## Rate Limiting

API requests are rate-limited to prevent abuse.

**Default Limits:**
- 60 requests per minute per IP address
- 1000 requests per hour per authenticated user

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 55
X-RateLimit-Reset: 1635516000
```

**Rate Limit Exceeded Response:**
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again later.",
    "status": 429
  },
  "meta": {
    "timestamp": "2025-10-29T19:00:00Z",
    "version": "1.0.0"
  }
}
```

---

## Examples

### cURL Examples

**Health Check:**
```bash
curl -X GET http://127.0.0.1:3000/api/health
```

**Get Metrics (with authentication):**
```bash
curl -X GET \
  http://127.0.0.1:3000/api/requestor/metrics \
  -H 'Cookie: laravel_session=your_session_token' \
  -H 'X-CSRF-TOKEN: your_csrf_token'
```

**Get Purchase Orders with Search:**
```bash
curl -X GET \
  'http://127.0.0.1:3000/api/requestor/purchase-orders?search=office&per_page=10' \
  -H 'Cookie: laravel_session=your_session_token' \
  -H 'X-CSRF-TOKEN: your_csrf_token'
```

### JavaScript (Fetch API) Examples

```javascript
// Get Metrics
fetch('/api/requestor/metrics', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  credentials: 'same-origin'
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

```javascript
// Get Purchase Orders
fetch('/api/requestor/purchase-orders?per_page=20&search=office', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  credentials: 'same-origin'
})
.then(response => response.json())
.then(data => {
  console.log('Total POs:', data.data.pagination.total);
  console.log('Items:', data.data.items);
})
.catch(error => console.error('Error:', error));
```

---

## Support

For API support and questions, contact the development team or refer to the main application documentation.

**Version History:**
- 1.0.0 (2025-10-29): Initial API release with requestor endpoints and health checks
