# Mayush Mobile Buyer MVP - Fixed User Flow Architecture

```
                                  [ App Launch ]
                                         │
                                         ▼
                             [ Language Selection ] ('fr' | 'ar')
                                         │
                                         ▼
                                   [ Home Screen ]
                                         │
                 ┌───────────────────────┴───────────────────────┐
                 ▼ (Direct Discovery)                             ▼ (Category Discovery)
        [ Product Details ]                             [ Categories Grid ]
                 │                                               │
                 │                                               ▼
                 │                                   [ Category Product List ]
                 │                                               │
                 └───────────────────────┬───────────────────────┘
                                         ▼
                             [ Product Details Screen ]
                                         │
                                         ▼
                             [ Variant Selector Sheet ]
                                         │
                                         ▼
                               [ Add to Guest Cart ]
                          (Persists returned `temp_user_id`)
                                         │
                                         ▼
                                  [ Cart Screen ]
                            (Subtotal, Shipping, Tax, Coupon)
                                         │
                                         ▼ (User Taps "Proceed to Checkout")
                            [ Authentication Gate ]
                      (Login or Registration Choice)
                                         │
                                         ▼ (Auth Credentials Submitted)
                              [ Server Cart Merge ]
                    (`UPDATE carts SET user_id = X WHERE temp_user_id = Y`)
                                         │
                                         ▼ (Token Saved, Cart Verified, `temp_user_id` Cleared)
                             [ Select/Add Address ]
                                         │
                                         ▼
                             [ Select Delivery Method ]
                                         │
                                         ▼
                             [ Select Payment Method ] (CMI | COD | Wallet)
                                         │
                                         ▼
                             [ Order Review Screen ]
                                         │
                                         ▼
                             [ POST /api/v2/order/store ]
                        (Server creates `CombinedOrder` #{combined_order_id})
                                         │
                 ┌───────────────────────┼───────────────────────┐
                 ▼ (COD)                 ▼ (Wallet)              ▼ (CMI Credit Card)
        [ Order Created ]        [ Balance Deducted ]    [ CMI Secure Mobile Bridge ]
          (Status: Unpaid)         (Status: Paid)        (Release Dependency)
                 │                       │                       │
                 │                       │                       ▼ (User Completes 3DS)
                 │                       │                [ CMI Server Callback ]
                 │                       │                (SHA-512 Hash Verified)
                 │                       │                       │
                 └───────────────────────┼───────────────────────┘
                                         ▼
                             [ Order Confirmation ]
```
