# Mayush Mobile Buyer MVP - Data & State Requirements

## 1. Storage & Persistence Requirements

| Data Key | Storage Target | Lifetime | Sensitivity |
| :--- | :--- | :--- | :--- |
| `app_language` | `AsyncStorage` | Permanent | Public (`'fr'` \| `'ar'`) |
| `temp_user_id` | `AsyncStorage` | Guest Session (Cleared after Cart Merge Verification) | Internal Token |
| `pending_merge_temp_user_id` | Memory / Storage | Transient during Auth Gate Merge | Internal Token |
| `access_token` | `SecureStore` (Expo) | Authenticated Session | High (Sanctum Bearer) |
| `user_profile` | `AsyncStorage` | Authenticated Session | Personal Profile Info |
| `active_cart_summary` | Memory | Transient | Financial Summary |

---

## 2. API Header Contracts

Every outgoing HTTP request from the mobile app MUST include:

```http
Accept: application/json
App-Language: fr | ar
Authorization: Bearer <access_token> (When authenticated)
```

> [!IMPORTANT]  
> `Content-Type: application/json` MUST be included **ONLY when the request contains a JSON request body** (e.g. `POST`, `PUT`, `PATCH`). It MUST NOT be included on bodyless `GET` requests, `DELETE` requests without body, or multipart file uploads.
