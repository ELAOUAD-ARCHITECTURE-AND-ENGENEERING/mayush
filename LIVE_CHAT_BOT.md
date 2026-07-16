# MAYUSH AUTOMATED SUPPORT CHATBOT

## Support Taxonomy and Conversation Logic Blueprint

---

# 1. Purpose

The Mayush chatbot will operate as the first support layer inside the existing Live Chat system.

Its responsibilities are to:

1. Welcome the visitor.
2. Detect or ask for the preferred language.
3. Understand the reason for contacting support.
4. Guide the customer through predefined support flows.
5. Retrieve verified information when authorized.
6. propose safe troubleshooting steps.
7. Confirm whether the problem was resolved.
8. Escalate unresolved or sensitive cases to a human agent.
9. Transfer the complete conversation context to the assigned administrator.
10. Learn from conversation outcomes through analytics.

The chatbot must never trap customers in repetitive automated conversations.

---

# 2. Core Operating Model

The chatbot should use a hybrid model:

## 2.1 Structured navigation

The chatbot presents predefined choices and buttons.

Example:

* Find a product
* Ask about a product
* Track an order
* Payment problem
* Delivery problem
* Return or refund
* Account problem
* Professional services
* Technical support
* Speak with an agent

Structured navigation should be the default because it is predictable, measurable, and safe.

## 2.2 Free-text understanding

Customers may write messages instead of selecting buttons.

Example:

> I paid for my order, but I cannot find it in my account.

The chatbot should map this message to:

```text
Category: Payment
Intent: Payment successful but order missing
Priority: High
```

Free-text detection should route the customer into a verified support flow. It should not generate unsupported answers.

## 2.3 Verified knowledge

Answers must come from:

* Approved Mayush support content
* Verified product information
* Authorized customer data
* Authorized order data
* Authorized payment status
* Approved delivery rules
* Approved return and refund policies

## 2.4 Human intervention

The chatbot must transfer the conversation when:

* The customer asks for an agent
* The issue is sensitive
* The issue requires manual approval
* The chatbot has low confidence
* Troubleshooting fails
* The required information is unavailable
* The customer becomes frustrated
* The conversation becomes too complex

---

# 3. Global Conversation Lifecycle

```text
NEW_CONVERSATION
      ↓
BOT_GREETING
      ↓
LANGUAGE_SELECTION
      ↓
USER_IDENTIFICATION
      ↓
INTENT_DETECTION
      ↓
CATEGORY_CONFIRMATION
      ↓
INFORMATION_COLLECTION
      ↓
RESOLUTION_ATTEMPT
      ↓
SATISFACTION_CHECK
     /                  \
RESOLVED          STILL_NEEDS_HELP
   ↓                      ↓
CLOSE          DEEPER_DIAGNOSTIC
                           ↓
                 SECOND_RESOLUTION
                           ↓
                 SATISFACTION_CHECK
                     /           \
                RESOLVED      ESCALATION
                                  ↓
                         WAITING_FOR_AGENT
                                  ↓
                           HUMAN_ASSIGNED
                                  ↓
                            HUMAN_ACTIVE
                                  ↓
                          RESOLVED_OR_CLOSED
```

---

# 4. Conversation States

The chatbot engine should support at least the following states:

| State                  | Meaning                                           |
| ---------------------- | ------------------------------------------------- |
| `NEW`                  | Conversation has been created                     |
| `BOT_GREETING`         | Bot is welcoming the customer                     |
| `LANGUAGE_SELECTION`   | Language is being detected or selected            |
| `INTENT_SELECTION`     | Main issue category is being identified           |
| `SUB_INTENT_SELECTION` | Specific issue is being identified                |
| `COLLECTING_DATA`      | Bot is collecting required information            |
| `VALIDATING_DATA`      | Submitted information is being checked            |
| `FETCHING_CONTEXT`     | Authorized Mayush information is being retrieved  |
| `PROVIDING_SOLUTION`   | Bot is presenting instructions or an answer       |
| `WAITING_CONFIRMATION` | Bot is asking whether the issue is resolved       |
| `SECOND_ATTEMPT`       | A deeper resolution path is active                |
| `ESCALATION_REQUIRED`  | Bot has determined that human support is required |
| `WAITING_FOR_AGENT`    | Conversation is in the support queue              |
| `AGENT_ASSIGNED`       | An administrator owns the conversation            |
| `HUMAN_ACTIVE`         | Human support is managing the conversation        |
| `PENDING_CUSTOMER`     | Waiting for the customer to respond               |
| `PENDING_SUPPORT`      | Waiting for internal support action               |
| `RESOLVED_BY_BOT`      | Bot resolved the issue                            |
| `RESOLVED_BY_AGENT`    | Human agent resolved the issue                    |
| `CLOSED`               | Conversation is closed                            |
| `ABANDONED`            | Customer left without completing the flow         |
| `SPAM`                 | Conversation was classified as spam               |

---

# 5. Initial Greeting

## 5.1 Default greeting

> Hello and welcome to Mayush 👋
> I’m the Mayush virtual assistant. I can help you find products, check your order, solve common problems, or connect you with our support team.

## 5.2 Language selection

The chatbot should detect the language of the first customer message.

When confidence is low, show:

* Français
* العربية
* English
* Darija

The selected language must remain active throughout the conversation unless the customer changes it.

## 5.3 Main menu

```text
How can I help you today?

1. Find a product
2. Ask about a product
3. My order
4. Payment problem
5. Delivery problem
6. Return, exchange, or refund
7. My account
8. Interior design or professional services
9. Technical problem
10. Complaint or security concern
11. Speak with a support agent
```

---

# 6. User Context

The chatbot should adapt its options according to the customer type.

## 6.1 Guest user

Available context:

* Guest session identifier
* Selected language
* Name, when provided
* Email or phone, when required
* Current page
* Current product
* Current cart, when safely accessible
* Conversation history for the active session

A guest must not access customer orders without completing an approved verification process.

## 6.2 Authenticated buyer

Available context may include:

* Customer ID
* Name
* Language
* Recent orders
* Open conversations
* Cart
* Wishlist
* Saved addresses
* Return requests
* Notification preferences

The chatbot must retrieve only information belonging to the authenticated customer.

## 6.3 Vendor or seller

The initial customer support chatbot should not expose administrative customer conversations to vendors.

Vendor support should use a separate support taxonomy or channel.

## 6.4 Administrator

Administrators should have access according to support permissions.

Not every administrator should automatically have access to:

* Payment disputes
* Customer identity information
* Private support notes
* Refund approvals
* Security complaints

---

# 7. Standard Intent Definition

Every support intent should follow this model:

```text
Intent ID
Intent name
Category
Description
Eligible user types
Trigger phrases
Initial question
Available choices
Required information
Optional information
Validation rules
Authorized data sources
Resolution steps
Success condition
Failure condition
Escalation condition
Priority
Target department
Customer-facing messages
Agent handoff summary
Analytics tags
```

---

# 8. Complete Support Taxonomy

---

# CATEGORY 1 — PRODUCT DISCOVERY

## Category ID

```text
PRODUCT_DISCOVERY
```

## Purpose

Help customers identify suitable products according to their needs.

## Main intents

### `PRODUCT_FIND_BY_CATEGORY`

Customer knows the general product category.

Choices:

* Furniture
* Lighting
* Decoration
* Kitchen
* Bathroom
* Office
* Outdoor
* Textiles
* Other

Required information:

* Product category
* Optional budget
* Optional room
* Optional style

Resolution:

* Show relevant categories
* Suggest filters
* Provide direct navigation links
* Offer to refine the search

Escalation:

* Customer needs personalized project consultation
* Product category does not exist
* Customer needs a custom product

---

### `PRODUCT_FIND_BY_ROOM`

Choices:

* Living room
* Bedroom
* Dining room
* Kitchen
* Bathroom
* Office
* Children’s room
* Terrace or garden
* Commercial space

Follow-up questions:

* What type of product are you looking for?
* What style do you prefer?
* What is your approximate budget?
* Are there size constraints?

Resolution:

* Return relevant products or collections
* Suggest complementary products

---

### `PRODUCT_FIND_BY_STYLE`

Possible styles:

* Modern
* Contemporary
* Minimalist
* Scandinavian
* Industrial
* Classic
* Moroccan
* Luxury
* Natural
* Customer is unsure

When the customer is unsure, ask visual preference questions rather than technical design terminology.

---

### `PRODUCT_FIND_BY_BUDGET`

Budget levels should be configurable by product category.

Example:

* Less than 1,000 MAD
* 1,000–3,000 MAD
* 3,000–7,000 MAD
* 7,000–15,000 MAD
* More than 15,000 MAD

The chatbot should not show fixed ranges that are unsuitable for the selected category.

---

### `PRODUCT_FIND_BY_DIMENSIONS`

Required information:

* Width
* Height
* Depth
* Measurement unit
* Maximum acceptable dimensions

Validation:

* Numeric values
* Positive measurements
* Supported units

Resolution:

* Filter products by dimensions
* Clearly warn when product dimensions are missing or approximate

---

### `PRODUCT_RECOMMENDATION`

The customer wants recommendations but does not know what to choose.

Collect:

* Room
* Product type
* Style
* Color preference
* Budget
* Dimensions
* Delivery city
* Intended use

Escalation:

* Complete furnishing project
* High-value purchase
* Professional or commercial project
* Custom dimensions
* Customer requests design advice

---

### `PRODUCT_MATCHING_ITEMS`

Examples:

* Matching chairs for a table
* Lighting suitable for a living room
* Decoration matching a sofa
* Complementary items from the same collection

The chatbot should use verified product relationships, categories, attributes, or curated collections.

It must not invent compatibility.

---

# CATEGORY 2 — PRODUCT INFORMATION

## Category ID

```text
PRODUCT_INFORMATION
```

## Main intents

### `PRODUCT_DIMENSIONS`

Return verified:

* Width
* Height
* Depth
* Weight
* Package dimensions, when available

When information is missing:

> I could not find verified dimensions for this product. I can transfer your request to our support team for confirmation.

---

### `PRODUCT_MATERIAL`

Possible information:

* Main material
* Secondary materials
* Finish
* Fabric
* Filling
* Frame material
* Surface treatment

Never infer materials from product images.

---

### `PRODUCT_COLOR_VARIANT`

Return:

* Available colors
* Available sizes
* Available models
* Variant stock

The chatbot must distinguish between:

* Variant exists
* Variant is currently available
* Variant is temporarily unavailable

---

### `PRODUCT_STOCK`

Possible responses:

* In stock
* Low stock
* Temporarily unavailable
* Pre-order
* Made to order
* Stock status unavailable

Stock must be fetched in real time or from the authoritative product service.

---

### `PRODUCT_DELIVERY_ESTIMATE`

Required context:

* Product
* Variant
* Quantity
* Delivery city
* Stock status
* Vendor or fulfilment source

The chatbot should provide estimates, not guarantees.

---

### `PRODUCT_ASSEMBLY`

Provide verified information about:

* Assembly required
* Professional installation available
* Assembly instructions
* Tools required
* Included hardware

Escalate when installation services require scheduling or quotation.

---

### `PRODUCT_CARE`

Possible topics:

* Cleaning
* Fabric care
* Wood care
* Metal care
* Marble care
* Outdoor protection
* Warranty preservation

Answers must come from approved care instructions.

---

### `PRODUCT_WARRANTY`

Return:

* Warranty availability
* Duration
* Covered defects
* Exclusions
* Claim procedure

Escalate warranty claims to a support agent after collecting order and product information.

---

### `PRODUCT_CUSTOMIZATION`

Collect:

* Product
* Requested dimensions
* Requested color
* Requested material
* Quantity
* City
* Deadline
* Budget range

All customization requests should become qualified leads and be transferred to the relevant team.

---

### `PRODUCT_COMPARISON`

Allow comparison based on verified fields:

* Price
* Dimensions
* Material
* Stock
* Delivery estimate
* Warranty
* Rating
* Seller

The chatbot should avoid declaring one product “better” without explaining the criteria.

---

# CATEGORY 3 — ACCOUNT AND AUTHENTICATION

## Category ID

```text
ACCOUNT_SUPPORT
```

## Main intents

### `ACCOUNT_CREATE_FAILED`

Possible causes:

* Invalid email
* Weak password
* Existing account
* Missing required field
* Terms not accepted
* Server error

Resolution:

* Identify displayed error
* Explain correction
* Retry registration
* Escalate technical failures

---

### `ACCOUNT_EMAIL_EXISTS`

Provide options:

* Sign in
* Reset password
* Continue with Google
* Contact support when the customer does not recognize the account

---

### `ACCOUNT_PHONE_EXISTS`

Do not reveal private account details.

Provide:

* Login
* OTP resend
* Account recovery
* Human verification when ownership is disputed

---

### `ACCOUNT_OTP_NOT_RECEIVED`

Troubleshooting:

1. Confirm country code.
2. Confirm phone number format.
3. Ask customer to wait briefly.
4. Check resend limit.
5. Ask whether SMS reception is available.
6. Offer another approved authentication method.

Escalate after the configured retry limit.

---

### `ACCOUNT_OTP_EXPIRED`

Resolution:

* Request a new OTP
* Explain the validity period
* Prevent repeated rapid resend requests

---

### `ACCOUNT_FORGOT_PASSWORD`

Guide through approved password reset.

Never ask for the current or previous password.

---

### `ACCOUNT_RESET_EMAIL_MISSING`

Troubleshooting:

* Confirm email
* Check spam folder
* Wait for delivery
* Retry within rate limits
* Verify whether social login was originally used

---

### `ACCOUNT_SOCIAL_LOGIN_FAILED`

Sub-intents:

* Google Sign-in failed
* Apple Sign-in failed
* Email conflicts with existing account
* Consent window closed
* Provider temporarily unavailable

Escalate repeated provider errors.

---

### `ACCOUNT_BLOCKED`

The chatbot should not reveal internal fraud or security rules.

Response:

> Your account requires additional verification. I’ll transfer this conversation to the appropriate support team.

Priority:

* Medium or high depending on the reason

---

### `ACCOUNT_UPDATE_PROFILE`

Help with:

* Name
* Phone
* Email
* Password
* Address
* Language
* Notification preferences

Sensitive changes may require re-authentication.

---

### `ACCOUNT_DELETE_REQUEST`

The bot may explain the process but should not immediately delete the account.

Collect:

* Request confirmation
* Reason, optionally
* Preferred contact channel

Escalate to the privacy or account-support process.

---

### `ACCOUNT_SUSPECTED_COMPROMISE`

Examples:

* Unknown login
* Unknown order
* Password changed
* Contact details changed

Immediate actions:

* Recommend password reset
* Recommend ending other sessions when supported
* Do not expose sensitive details
* Escalate immediately

Priority:

```text
CRITICAL
```

---

# CATEGORY 4 — CART, PROMOTIONS, AND CHECKOUT

## Category ID

```text
CHECKOUT_SUPPORT
```

## Main intents

### `CART_ADD_FAILED`

Collect:

* Product
* Variant
* Quantity
* Error message
* Device or browser when needed

Possible causes:

* Product unavailable
* Invalid variant
* Quantity exceeds stock
* Session problem
* Technical failure

---

### `CART_PRODUCT_DISAPPEARED`

Possible explanations:

* Product became unavailable
* Variant was removed
* Session expired
* Cart synchronized after login
* Seller disabled product

The chatbot must verify before explaining.

---

### `CART_WRONG_QUANTITY`

Guide the customer to update quantity.

Escalate when quantity changes automatically or stock values conflict.

---

### `CART_PRICE_CHANGED`

Check:

* Product price
* Variant price
* Promotion expiration
* Tax or delivery additions
* Vendor price update

Never accuse the customer of misunderstanding without showing the price breakdown.

---

### `COUPON_INVALID`

Possible causes:

* Coupon expired
* Minimum purchase not reached
* Product excluded
* Category excluded
* Customer not eligible
* Usage limit reached
* Coupon already used
* Incorrect code

The chatbot should return only the reason authorized for customer display.

---

### `DELIVERY_COST_UNEXPECTED`

Explain the delivery calculation using:

* City
* Product size
* Vendor
* Quantity
* Free-delivery threshold
* Special handling

Escalate unexplained cost differences.

---

### `CHECKOUT_ADDRESS_FAILED`

Possible situations:

* City unsupported
* Required fields missing
* Postal code invalid
* Address format invalid
* Saved address cannot be selected

---

### `CHECKOUT_PAGE_ERROR`

Collect:

* Page or step
* Error message
* Device
* Browser
* Approximate time
* Product or cart context

Provide safe troubleshooting:

* Refresh once
* Check connection
* Retry in a private session only when appropriate
* Avoid repeated payment attempts

---

### `EXPRESS_BUY_FAILED`

Possible causes:

* Product unavailable
* Missing address
* Authentication required
* Payment method unavailable
* Checkout lock
* Technical error

Escalate when an order or payment may already have been created.

---

# CATEGORY 5 — PAYMENT SUPPORT

## Category ID

```text
PAYMENT_SUPPORT
```

## Security warning

The bot must display when relevant:

> Never share your full card number, CVV, banking password, or bank verification code in this chat.

## Main intents

### `PAYMENT_DECLINED`

Collect:

* Order reference, when available
* Payment method
* Approximate time
* Error message

Safe guidance:

* Verify card authorization
* Verify spending limit
* Verify online-payment activation
* Retry only once when no charge exists
* Use another approved payment method

Escalate after repeated failure or conflicting status.

---

### `PAYMENT_PENDING`

Possible states:

* Payment authorization pending
* Mayush verification pending
* Order pending
* Bank response delayed

The chatbot should retrieve the actual payment state before answering.

Do not advise another payment attempt while the first attempt may still be pending.

---

### `PAYMENT_SUCCESS_ORDER_MISSING`

Required information:

* Authenticated customer
* Approximate payment time
* Amount
* Email or phone
* Order reference, when available
* Payment reference safe for customer support

Bot actions:

1. Search for a matching recent order.
2. Check approved payment status.
3. Check whether order creation is delayed.
4. Avoid asking the customer to pay again.

Escalation:

```text
ALWAYS ESCALATE
```

Priority:

```text
HIGH
```

---

### `PAYMENT_DUPLICATE_CHARGE`

Collect:

* Order reference
* Number of charges
* Amounts
* Approximate dates
* Last four digits only when approved

Do not promise an immediate refund.

Escalate to payment support.

Priority:

```text
HIGH
```

---

### `PAYMENT_PAGE_NOT_LOADING`

Troubleshooting:

* Confirm connection
* Confirm browser compatibility
* Disable aggressive popup blocking when relevant
* Retry from checkout
* Do not repeat payment after a possible charge

Escalate when repeated or when payment status is uncertain.

---

### `PAYMENT_RETURN_NO_CONFIRMATION`

Customer returned from the payment gateway without confirmation.

Bot should:

* Check order status
* Check payment status
* Ask customer not to repeat payment until verification finishes
* Escalate conflicting states

---

### `PAYMENT_AMOUNT_INCORRECT`

Possible causes:

* Delivery fees
* Taxes
* Discount removed
* Quantity changed
* Variant price
* Currency or rounding
* Duplicate order

Return a verified breakdown.

Escalate mismatches.

---

### `PAYMENT_METHOD_CHANGE`

The bot may explain available methods.

Payment method changes after order creation should follow approved business rules.

---

### `PAYMENT_RECEIPT_REQUEST`

Provide:

* Invoice or receipt access instructions
* Order reference
* Download link when authorized

Escalate requests for corrected company billing information.

---

### `PAYMENT_FRAUD_REPORT`

Examples:

* Unknown Mayush charge
* Stolen card concern
* Suspicious transaction

Immediate instructions:

* Contact the bank
* Secure Mayush account
* Do not share card details
* Transfer to security or payment support

Priority:

```text
CRITICAL
```

---

# CATEGORY 6 — ORDER SUPPORT

## Category ID

```text
ORDER_SUPPORT
```

## Main intents

### `ORDER_CONFIRMATION_MISSING`

Check:

* Recent order
* Email address
* Order status
* Notification delivery

Resolution:

* Display order reference
* Resend confirmation when allowed
* Explain pending status
* Escalate missing order after payment

---

### `ORDER_STATUS`

Possible customer-facing statuses:

* Awaiting payment
* Payment verification
* Confirmed
* Preparing
* Partially prepared
* Shipped
* Partially shipped
* Delivered
* Cancelled
* Refund in progress
* Completed

Internal statuses should be translated into understandable customer language.

---

### `ORDER_NOT_VISIBLE`

Possible causes:

* Guest checkout
* Different account
* Different email
* Delayed order creation
* Order archived
* Authentication mismatch

Do not reveal orders until ownership is verified.

---

### `ORDER_CHANGE_ADDRESS`

Decision rules:

* Before processing: change may be possible
* During preparation: manual approval may be required
* After shipping: usually not guaranteed

Escalate when fulfilment has started.

---

### `ORDER_CHANGE_PRODUCT`

Potential changes:

* Variant
* Color
* Size
* Quantity
* Replace product

The bot should not modify confirmed orders without approved rules.

---

### `ORDER_CANCEL`

Check:

* Payment status
* Preparation status
* Shipping status
* Vendor status
* Cancellation eligibility

Possible result:

* Cancellation available
* Manual approval required
* Cancellation no longer available
* Return process required after delivery

---

### `ORDER_PARTIAL_SHIPMENT`

Explain when products originate from different sellers or fulfilment locations.

Show shipment-specific information when authorized.

---

### `ORDER_ITEM_MISSING`

Collect:

* Order
* Shipment
* Missing product
* Expected quantity
* Received quantity
* Packaging condition

Escalate for investigation.

---

### `ORDER_GUEST_LOOKUP`

Require secure verification using approved fields.

Possible verification combination:

* Order reference
* Email or phone
* Signed guest-order token

Never rely only on an order number.

---

# CATEGORY 7 — DELIVERY SUPPORT

## Category ID

```text
DELIVERY_SUPPORT
```

## Main intents

### `DELIVERY_AREA`

Collect city or region.

Return:

* Delivery available
* Delivery unavailable
* Manual quotation required
* Special delivery conditions

---

### `DELIVERY_PRICE`

Calculate from approved rules or provide an estimate.

Never invent a price.

---

### `DELIVERY_ESTIMATE`

Required context:

* Product
* Stock
* Vendor
* Order status
* Destination
* Delivery method

Use date ranges rather than guaranteed dates unless a confirmed appointment exists.

---

### `DELIVERY_DELAY`

Bot actions:

1. Retrieve expected delivery period.
2. Retrieve current status.
3. Compare expected and actual progress.
4. Provide the latest verified update.
5. Escalate significant delays.

---

### `DELIVERY_TRACKING_MISSING`

Possible reasons:

* Order not yet shipped
* Tracking not synchronized
* Vendor delivery
* Internal delivery
* Carrier delay

Escalate when shipment exists but tracking remains unavailable.

---

### `DELIVERY_ADDRESS_CHANGE`

Follow the same shipment-stage rules as order address changes.

---

### `DELIVERY_CUSTOMER_ABSENT`

Provide:

* Redelivery process
* Carrier contact process
* Pickup option, when available
* Possible redelivery charges

---

### `DELIVERY_COURIER_NO_CONTACT`

Collect:

* Order
* Expected date
* Phone number confirmation
* Carrier information

Escalate after the expected delivery window.

---

### `DELIVERY_MARKED_DELIVERED_NOT_RECEIVED`

Immediate actions:

* Confirm delivery address
* Ask whether household or building staff received it
* Check proof of delivery
* Avoid blaming the customer
* Escalate immediately

Priority:

```text
HIGH
```

---

### `DELIVERY_DAMAGED_PACKAGE`

Collect:

* Order
* Product
* Delivery date
* Packaging condition
* Photos when supported
* Whether the delivery was accepted

Escalate to claims support.

---

### `DELIVERY_ACCESS_CONSTRAINT`

Examples:

* No elevator
* Narrow staircase
* Upper floor
* Restricted building access
* Parking restrictions
* Large furniture

Collect access information before scheduling.

---

### `DELIVERY_INSTALLATION`

Explain whether:

* Installation is included
* Installation is optional
* Separate scheduling is required
* Additional fees apply

Escalate quotation or appointment requests.

---

# CATEGORY 8 — RETURNS, EXCHANGES, AND REFUNDS

## Category ID

```text
AFTER_SALES_SUPPORT
```

## Main intents

### `RETURN_ELIGIBILITY`

Check:

* Order
* Product
* Delivery date
* Return period
* Product condition
* Product category
* Customization
* Assembly state
* Packaging
* Hygiene exclusions

The chatbot may explain eligibility but should not override policy.

---

### `RETURN_START_REQUEST`

Collect:

* Order
* Product
* Quantity
* Reason
* Condition
* Photos when required

Possible outcomes:

* Return request created
* Manual review required
* Product not eligible
* Additional information required

---

### `EXCHANGE_REQUEST`

Collect:

* Product
* Current variant
* Requested variant
* Stock
* Price difference
* Product condition

Escalate exchanges requiring payment adjustment or manual logistics.

---

### `WRONG_PRODUCT_RECEIVED`

Collect:

* Ordered product
* Received product
* Quantity
* Delivery date
* Photos
* Packaging label

Priority:

```text
HIGH
```

---

### `DAMAGED_PRODUCT`

Collect:

* Product
* Damage type
* Delivery date
* Packaging condition
* Photos
* Whether product was assembled

Escalate for validation.

---

### `DEFECTIVE_PRODUCT`

Distinguish:

* Damage during delivery
* Manufacturing defect
* Normal wear
* Assembly problem
* Incorrect use

Escalate warranty assessment.

---

### `PRODUCT_NOT_AS_DESCRIBED`

Collect:

* Product page
* Expected characteristic
* Actual difference
* Images
* Description

Escalate to after-sales and catalog-quality teams.

---

### `REFUND_STATUS`

Return verified status:

* Not requested
* Awaiting approval
* Approved
* Processing
* Sent
* Failed
* Completed

Explain that bank processing times may differ.

---

### `REFUND_DELAY`

Check:

* Approval date
* Processing date
* Payment method
* Expected financial processing period
* Failed refund state

Escalate when the expected period has passed.

---

### `RETURN_REJECTED`

Explain the recorded reason when authorized.

Offer human review when the customer disputes the decision.

---

# CATEGORY 9 — PROFESSIONAL AND INTERIOR DESIGN SERVICES

## Category ID

```text
PROFESSIONAL_SERVICES
```

## Main intents

### `INTERIOR_DESIGN_CONSULTATION`

Collect:

* Project type
* City
* Surface area
* Rooms
* Style
* Budget
* Timeline
* Current project stage
* Preferred contact method

Transfer as a qualified lead.

---

### `FULL_INTERIOR_PROJECT`

Project types:

* Apartment
* Villa
* Office
* Retail
* Restaurant
* Hotel
* Other professional space

Collect detailed requirements and escalate to the design team.

---

### `PRODUCT_SOURCING_SERVICE`

Collect:

* Product categories
* Quantity
* Style
* Budget
* Timeline
* Delivery city
* Professional or personal project

---

### `BULK_ORDER`

Collect:

* Product
* Quantity
* Company
* Required date
* Billing information
* Delivery destination

Transfer to sales support.

---

### `PROFESSIONAL_ACCOUNT`

Possible users:

* Interior designer
* Architect
* Contractor
* Company buyer
* Hotel or restaurant
* Property developer

Explain benefits only from approved program information.

---

### `VENDOR_REGISTRATION`

Collect:

* Company
* Product categories
* City
* Website or catalog
* Contact information
* Legal status

Transfer to vendor onboarding.

---

### `CUSTOM_PRODUCT_REQUEST`

Collect:

* Product type
* Dimensions
* Material
* Color
* Quantity
* Budget
* Reference images
* Deadline

Always require human follow-up.

---

# CATEGORY 10 — TECHNICAL SUPPORT

## Category ID

```text
TECHNICAL_SUPPORT
```

## Main intents

### `PAGE_NOT_LOADING`

Collect:

* URL or page
* Device
* Browser
* Error
* Connection type
* Approximate time

---

### `BLANK_PAGE`

Provide limited troubleshooting and collect diagnostic context.

Escalate repeated occurrences.

---

### `SEARCH_NOT_WORKING`

Collect:

* Search term
* Filters
* Expected result
* Actual result

Potentially tag the case for search-quality analysis.

---

### `IMAGE_NOT_LOADING`

Collect:

* Product
* Image position
* Device
* Browser

Provide fallback information without inventing visual details.

---

### `BUTTON_NOT_WORKING`

Collect:

* Button
* Page
* Action expected
* Device
* Error message

---

### `MOBILE_LAYOUT_PROBLEM`

Collect:

* Device model
* Operating system
* Browser
* Screen orientation
* Screenshot
* Page

---

### `WEBSITE_SLOW`

Collect:

* Page
* Device
* Connection
* Approximate time
* Whether all pages are affected

Avoid blaming the customer’s connection without evidence.

---

### `UNEXPECTED_ERROR`

Collect:

* Exact message
* Steps before error
* Page
* Time
* Device
* Browser

Generate an internal technical incident summary.

---

### `CHAT_NOT_WORKING`

Possible situations:

* Message not sent
* Reconnection loop
* Widget not opening
* Agent response not visible
* Notification missing
* Duplicate messages

The Live Chat should have a fallback support channel when the chatbot itself fails.

---

# CATEGORY 11 — COMPLAINTS, PRIVACY, AND SECURITY

## Category ID

```text
SENSITIVE_SUPPORT
```

## Main intents

### `CUSTOMER_SERVICE_COMPLAINT`

Collect:

* Order or conversation
* Date
* Complaint summary
* Desired outcome

Escalate to a supervisor when serious.

---

### `VENDOR_COMPLAINT`

Collect:

* Vendor
* Product
* Order
* Nature of complaint
* Evidence

Do not expose internal vendor details.

---

### `DELIVERY_AGENT_COMPLAINT`

Collect:

* Order
* Delivery date
* Incident
* Carrier or agent, when known

Escalate to logistics management.

---

### `PRODUCT_AUTHENTICITY_CONCERN`

Priority:

```text
HIGH
```

Collect:

* Product
* Seller
* Order
* Reason for concern
* Photos or evidence

---

### `PRIVACY_REQUEST`

Sub-intents:

* Access personal data
* Correct personal data
* Delete personal data
* Restrict processing
* Withdraw marketing consent
* Report data exposure

Transfer to the approved privacy process.

---

### `SECURITY_REPORT`

Examples:

* Account takeover
* Data exposure
* Suspicious link
* Vulnerability report
* Unauthorized access

Immediate escalation:

```text
CRITICAL
```

Do not ask the customer to publish sensitive security details in the general chat.

---

### `LEGAL_COMPLAINT`

The chatbot should collect minimal information and transfer immediately.

It should not provide legal conclusions.

---

### `ABUSIVE_OR_THREATENING_CONVERSATION`

The bot should:

* Remain neutral
* Avoid confrontation
* Warn about respectful communication when appropriate
* Escalate credible threats
* Allow administrators to block or restrict abuse

---

# 9. Universal Escalation Rules

## 9.1 Customer-requested escalation

Escalate immediately when the customer says:

* Speak to a human
* Speak to an agent
* Contact support
* I do not want the bot
* I need a manager
* This is not helping

The customer should not be forced to justify the request.

---

## 9.2 Understanding failure

Recommended initial thresholds:

```text
Maximum unrecognized messages: 2
Maximum category corrections: 2
Maximum repeated question cycles: 2
```

After reaching the threshold:

> I’m sorry, I could not understand the issue accurately enough. I’ll transfer the conversation to a support agent so you do not have to repeat everything.

---

## 9.3 Resolution failure

Recommended rule:

```text
First verified solution fails
        ↓
One deeper diagnostic attempt
        ↓
Second verified solution fails
        ↓
Escalate to human support
```

Sensitive categories may skip the second attempt.

---

## 9.4 Conversation depth

Trigger reevaluation when:

* Bot turns exceed 12
* Customer repeatedly returns to the same menu
* More than two intents are active
* The customer gives contradictory answers
* The flow cannot reach a supported resolution

Conversation depth should not be the only escalation signal.

---

## 9.5 Immediate escalation cases

* Payment charged without order
* Duplicate charge
* Suspected fraud
* Account compromise
* Security report
* Privacy complaint
* Legal complaint
* Delivered but not received
* Damaged or incorrect product
* Refund dispute
* Product authenticity concern
* Customer explicitly requests an agent
* Customer threatens self-harm, violence, or serious harm
* Bot lacks verified information
* Required internal service is unavailable

---

# 10. Customer Sentiment Rules

The bot may detect frustration from phrases such as:

* This is useless
* You keep repeating
* I already explained
* I am angry
* This is unacceptable
* I want to complain
* Nobody is helping me
* I will report this
* I want a refund now

Possible response:

> I understand that this situation is frustrating. I’m transferring your conversation to a support agent with the details already collected.

Sentiment detection must not be used to deny service or classify the customer negatively.

---

# 11. Human Handoff

## 11.1 Handoff message to customer

> I’m transferring your conversation to a Mayush support agent. You do not need to repeat the information you already provided.

When no agent is immediately available:

> Your request has been added to the support queue. A Mayush agent will review the conversation and respond here as soon as possible.

Do not promise an exact response time unless the service-level data is reliable.

## 11.2 Handoff package

The administrator should receive:

```text
Conversation ID
Customer ID or secure guest identity
Customer name
Language
Main category
Detected intent
Confidence score
Conversation summary
Information collected
Order reference
Product references
Payment context without sensitive information
Troubleshooting steps already attempted
Customer answers
Customer sentiment
Escalation reason
Priority
Suggested department
Recommended next action
```

## 11.3 Bot behavior after handoff

When an agent accepts the conversation:

```text
conversation_mode = HUMAN
bot_enabled = false
assigned_agent_id = agent
handoff_time = timestamp
```

The bot must stop responding automatically.

It may continue performing invisible functions such as:

* Summarizing
* Suggesting replies privately to the agent
* Detecting related knowledge articles
* Classifying the case

These actions must never send messages directly without agent approval.

---

# 12. Conversation Priority

| Priority | Examples                                                                         |
| -------- | -------------------------------------------------------------------------------- |
| Critical | Fraud, account compromise, security breach, privacy exposure                     |
| High     | Charged without order, duplicate charge, delivered but missing, damaged product  |
| Medium   | Delivery delay, account access failure, return dispute, technical checkout issue |
| Normal   | Product information, recommendations, general delivery questions                 |
| Low      | General browsing assistance, informational questions                             |

Priority must affect queue ordering but should not bypass authorization or ownership controls.

---

# 13. Bot Response Rules

Every response should be:

* Short
* Clear
* Friendly
* Action-oriented
* Based on verified information
* Appropriate to the selected language
* Free from technical jargon
* Transparent when information is unavailable

The bot should avoid:

* Large paragraphs
* Too many choices at once
* Repeating the full conversation
* Blaming the customer
* Promising unapproved outcomes
* Pretending certainty
* Sending several messages rapidly

Recommended maximum:

```text
Main menu choices: 6–10
Submenu choices: 3–7
Troubleshooting steps per message: 3–5
Questions per message: 1–2
```

---

# 14. Knowledge Base Structure

Each chatbot knowledge article should include:

```text
Article ID
Title
Category
Intent
Language
Customer type
Approved answer
Step-by-step instructions
Applicable conditions
Excluded conditions
Required warnings
Related links
Escalation condition
Status
Version
Owner
Last review date
Next review date
```

Article statuses:

* Draft
* Under review
* Approved
* Published
* Disabled
* Expired

Expired or unapproved content must not be used.

---

# 15. Dynamic Data Access Rules

## 15.1 Product data

The bot may retrieve:

* Name
* Public product reference
* Price
* Variant
* Stock
* Dimensions
* Material
* Delivery estimate
* Warranty information

## 15.2 Customer data

The bot may retrieve only what is necessary:

* Name
* Language
* Contact method
* Customer’s own orders
* Customer’s own returns
* Customer’s open conversations

## 15.3 Order data

The bot may retrieve:

* Order reference
* Customer-facing status
* Products
* Quantities
* Delivery status
* Refund status
* Payment state

## 15.4 Restricted information

The bot must never expose:

* Full card data
* CVV
* CMI tokens
* Passwords
* Authentication secrets
* Internal fraud scores
* Internal security notes
* Another customer’s data
* Private vendor information
* Unnecessary payment references
* Internal database identifiers

---

# 16. Conversation Variables

Common variables should include:

```text
customer_type
customer_id
guest_token
language
current_page
current_product_id
current_order_id
intent
sub_intent
intent_confidence
conversation_state
resolution_attempts
failed_understanding_count
sentiment
priority
assigned_department
assigned_agent
collected_information
escalation_reason
bot_resolution_status
```

Variables must be scoped to the active conversation.

---

# 17. Analytics

The chatbot should track:

* Conversations started
* Guest conversations
* Authenticated conversations
* Selected language
* Detected intents
* Intent-confidence distribution
* Menu usage
* Bot resolution rate
* Escalation rate
* Customer-requested escalation
* Failed understanding rate
* Average bot turns
* Average time before escalation
* Abandoned conversations
* Satisfaction score
* Most common support cases
* Most common failed flows
* Agent response time after escalation
* Reopened conversations
* Knowledge articles used
* Knowledge articles associated with failed resolutions

Message content should not be logged unnecessarily in analytics.

---

# 18. Initial MVP Scope

The first advanced chatbot version should prioritize:

## Priority 1

* Friendly greeting
* Language selection
* Main support menu
* Human-agent request
* Order tracking
* Order status
* Payment pending
* Payment successful but order missing
* Delivery status
* Delivery delay
* Return eligibility
* Refund status
* Login and password problems
* Product information
* Product availability

## Priority 2

* Product recommendations
* Coupon problems
* Cart problems
* Damaged product
* Incorrect product
* Guest order lookup
* Professional-service lead collection
* Technical support
* Complaint management

## Priority 3

* Advanced product matching
* Multi-intent conversations
* Sentiment-based escalation
* Agent reply suggestions
* AI-generated summaries
* Personalized recommendations
* Multilingual free-text understanding
* Advanced analytics
* Automated support-flow optimization

---

# 19. Features to Postpone

The following should not be part of the first version:

* Fully generative unrestricted answers
* Automatic refund approval
* Automatic payment corrections
* Automatic order cancellation without validation
* Autonomous return approval
* Automatic vendor dispute decisions
* Financial advice
* Legal advice
* Automatic handling of security incidents
* Voice support
* Complex image analysis
* Autonomous modification of customer data
* Bot access to unrestricted administrative data

---

# 20. Acceptance Criteria

The chatbot logic is ready for technical implementation when:

1. Every main support category has an owner.
2. Every intent has a unique identifier.
3. Required customer data is clearly defined.
4. All answers have an approved source.
5. Escalation rules are documented.
6. Sensitive cases are identified.
7. Guest and authenticated-user flows are separated.
8. Agent handoff information is defined.
9. Conversation states and transitions are approved.
10. Bot resolution limits are defined.
11. Knowledge-base content ownership is assigned.
12. Multilingual requirements are approved.
13. Dynamic data permissions are documented.
14. Privacy and payment restrictions are documented.
15. Analytics events are defined.
16. MVP intents are selected.
17. Human takeover stops automated responses.
18. The customer can request an agent at any time.

---

# 21. Recommended Next Technical Design

The next phase should convert this taxonomy into a technical chatbot engine specification covering:

* Database entities
* Flow and node structure
* State machine
* Intent-matching strategy
* Knowledge-base architecture
* Bot message templates
* Dynamic Mayush actions
* Escalation engine
* Agent takeover
* Admin flow editor
* API contracts
* Events and queues
* Security policies
* Testing strategy
* Migration and rollout plan

No implementation should begin until the MVP intents, support policies, and escalation ownership are approved.
