# API Response Strategy Matrix

This document maps each controller method to its response strategy for maintaining legacy API contract compatibility.

## Response Strategies

1. **passthrough** - Returns raw data without envelope
2. **paginated** - Returns paginated envelope with `data`, `total`, `page`, `per_page`, `total_pages`
3. **legacy_message** - Returns legacy mutation response with `success`, `message`, optional `data` and `error_code`
4. **default** - Returns full envelope with `success`, `data`, optional `message` and `error_code`

## Controller Method Mapping

### Calendar API
| Method | Endpoint | Strategy | Notes |
|--------|----------|----------|-------|
| `get_calendar` | `/calendar` | **passthrough** | Returns raw array with `cases`, `progress`, `date_index` keys |
| Error responses | All | **default** | Validation errors use default envelope |

### Analytics API
| Method | Endpoint | Strategy | Notes |
|--------|----------|----------|-------|
| `get_analytics` | `/analytics` | **passthrough** | Returns raw array with `summary`, `customer_stats`, `admin_stats`, `trends` |

### Cases API
| Method | Endpoint | Strategy | Notes |
|--------|----------|----------|-------|
| `read` | `/cases/{id_user}` | **paginated** | Paginated list of cases for user |
| `search_cases` | `/cases/search` | **paginated** | Paginated search results |
| `create` | `/cases` | **legacy_message** | Mutation response with `success`, `message`, `id` in data |
| `update` | `/cases/{id}` | **legacy_message** | Mutation response (returns int/false/null directly) |
| `delete` | `/cases/{id}` | **legacy_message** | Mutation response |

### Users API
| Method | Endpoint | Strategy | Notes |
|--------|----------|----------|-------|
| `read` | `/users` | **paginated** | Paginated list of users |
| `search_users` | `/users/search` | **paginated** | Paginated search results |
| `read_staff` | `/users/staff` | **passthrough** | Raw array of staff users |
| `create` | `/users` | **legacy_message** | Mutation response |
| `update` | `/users/{id}` | **legacy_message** | Mutation response |
| `delete` | `/users/{id}` | **legacy_message** | Mutation response |

### Progress API
| Method | Endpoint | Strategy | Notes |
|--------|----------|----------|-------|
| `read` | `/progress/{id_case}` | **passthrough** | Raw array of progress entries |
| `read_user_attachments` | `/progress/user-attachments/{id_user}` | **passthrough** | Raw array of user attachments |
| `create` | `/progress/{id_case}` | **legacy_message** | Mutation response |
| `update` | `/progress/{id}` | **legacy_message** | Mutation response |
| `delete` | `/progress/{id}` | **legacy_message** | Mutation response |
| `upload_file` | `/progress/upload` | **legacy_message** | Mutation response |

### Toggle API
| Method | Endpoint | Strategy | Notes |
|--------|----------|----------|-------|
| `toggle` | `/toggle/{id}` | **legacy_message** | Mutation response |

## Implementation Status

### ✅ Completed
- [x] Created `STOLMC_Service_Tracker_Api_Response_Mapper` class
- [x] Updated base `STOLMC_Service_Tracker_Api` class with mapper support
- [x] Calendar API: Updated `get_calendar` to use `to_passthrough_response()`
- [x] Calendar API: Updated error responses to use `to_default_response()`
- [x] Analytics API: Updated `get_analytics` to use `to_passthrough_response()`
- [x] Cases API: Updated `read` to use `to_paginated_response()`
- [x] Cases API: Updated `create` to use `to_legacy_message_response()`

### 🔄 In Progress
- [ ] Update remaining `rest_response()` calls in Cases API
- [ ] Update Users API responses
- [ ] Update Progress API responses  
- [ ] Update Toggle API responses
- [ ] Add contract regression tests
- [ ] Implement snapshot-like assertions

## Test Requirements

### Contract Regression Tests Needed
- [ ] Calendar success + validation error
- [ ] Analytics success + error
- [ ] Cases read/search success/error
- [ ] Users read/search success/error
- [ ] Progress read/read_user_attachments success/error
- [ ] One mutation each family (cases/users/progress/toggle/upload) success/error

### Snapshot-like Assertions
Each test must assert:
- Top-level keys
- Nested keys for envelope fields
- Key absence where required
- Exact status code

## Compatibility Checks
- [ ] Calendar success has top-level `cases` and `progress` keys
- [ ] Paginated endpoints preserve `data`, `total`, `page`, `per_page`, `total_pages` exactly
- [ ] Legacy message endpoints preserve `success` and `message`
- [ ] Existing API contract tests pass with no payload/status regressions