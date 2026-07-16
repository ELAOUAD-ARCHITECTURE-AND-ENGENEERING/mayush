# MAYUSH CHATBOT ISSUE-CASE DATABASE

## 1. Database objective

This database will allow the Mayush chatbot to:

1. Identify the customer’s problem.
2. Map different question formulations to the same issue.
3. Collect the information required to investigate.
4. Retrieve verified Mayush data when needed.
5. propose an approved solution.
6. Confirm whether the solution worked.
7. Transfer unresolved or sensitive issues to a human agent.
8. Send the agent a structured summary of the case.

---

# 2. Standard issue-case structure

Every chatbot case should contain the following fields:

| Field                  | Purpose                                                            |
| ---------------------- | ------------------------------------------------------------------ |
| `case_id`              | Unique issue identifier                                            |
| `category_id`          | Main support category                                              |
| `case_name`            | Human-readable issue name                                          |
| `description`          | Explanation of the customer’s situation                            |
| `eligible_users`       | Guest, buyer, vendor, professional, or all                         |
| `question_variants`    | Different ways customers may describe the issue                    |
| `trigger_keywords`     | Keywords that help detect the intent                               |
| `required_information` | Information needed to resolve the case                             |
| `optional_information` | Additional useful context                                          |
| `validation_rules`     | Conditions for accepting the customer’s data                       |
| `data_source`          | Knowledge base, product data, order service, payment service, etc. |
| `resolution_steps`     | Steps performed by the chatbot                                     |
| `bot_answer`           | Approved customer-facing response                                  |
| `success_condition`    | How the bot knows the issue is resolved                            |
| `failure_condition`    | When the automated solution failed                                 |
| `escalation_rule`      | When to send the case to an agent                                  |
| `priority`             | Low, normal, medium, high, or critical                             |
| `department`           | Support team that should handle escalation                         |
| `related_cases`        | Similar cases the bot may propose                                  |
| `analytics_tags`       | Tags used for reports and optimization                             |

---

# 3. Main issue map

```text
MAYUSH SUPPORT
│
├── 1. Product Discovery
├── 2. Product Information
├── 3. Account and Authentication
├── 4. Cart, Promotions, and Checkout
├── 5. Payment
├── 6. Orders
├── 7. Delivery
├── 8. Returns, Exchanges, and Refunds
├── 9. Interior Design and Professional Services
├── 10. Technical Problems
├── 11. Complaints
├── 12. Privacy and Security
└── 13. Human Support
```

---

# 4. Product discovery cases

## `PD-001` — Find a product by category

### Possible customer questions

* Do you sell sofas?
* I am looking for lighting.
* Where can I find office furniture?
* Show me dining tables.
* Do you have bathroom products?
* I need furniture for my bedroom.
* Which product categories are available?

### Case explanation

The customer knows the general type of product but has not selected a specific item.

### Required information

* Product category
* Optional subcategory
* Optional budget
* Optional delivery city

### Bot resolution

1. Identify the product category.
2. Ask for a subcategory when the category is broad.
3. Show matching products or category links.
4. Offer filters such as budget, color, material, and dimensions.

### Successful result

The customer opens a product or category page.

### Escalation

Escalate when:

* The customer needs a custom item.
* No matching category exists.
* The customer needs help furnishing an entire project.

---

## `PD-002` — Find a product by room

### Possible questions

* What furniture do I need for my living room?
* Show me products for a bedroom.
* I need to furnish an office.
* What can I buy for my terrace?
* Help me decorate a small apartment.
* I need furniture for a restaurant.

### Case explanation

The customer starts from the space instead of a specific product.

### Required information

* Room type
* Approximate size
* Style
* Budget
* Main objective

### Bot resolution

The bot proposes suitable product families.

Example for a living room:

* Sofa
* Coffee table
* Television unit
* Rug
* Lighting
* Decoration
* Storage

### Escalation

Escalate complete furnishing, commercial projects, or complex space-planning requests to the design team.

---

## `PD-003` — Find a product by style

### Possible questions

* I want a modern sofa.
* Show me Scandinavian furniture.
* Do you have Moroccan-style decoration?
* I need something luxurious.
* What products match an industrial interior?
* I do not know which style suits my home.

### Required information

* Preferred style
* Room
* Product category
* Color preferences
* Budget

### Resolution

1. Show available style options.
2. Present matching collections or filtered results.
3. Ask the customer whether they prefer warmer, lighter, darker, simpler, or more decorative designs when they do not know the style name.

### Escalation

Escalate when the customer needs a professional style consultation.

---

## `PD-004` — Find a product by budget

### Possible questions

* What can I buy for less than 2,000 MAD?
* Show me affordable sofas.
* I have a budget of 10,000 MAD for my living room.
* What is the cheapest dining table?
* I want premium furniture.
* Help me furnish a room within my budget.

### Required information

* Total or item-level budget
* Product category
* Room
* Required quantity

### Resolution

The bot filters products by current verified prices.

The bot must clearly distinguish between:

* Product price
* Delivery cost
* Installation cost
* Total project budget

### Escalation

Escalate large furnishing budgets or projects involving several rooms.

---

## `PD-005` — Find a product by dimensions

### Possible questions

* I need a sofa less than two meters wide.
* Which table fits a 3 × 4-meter room?
* Do you have a cabinet 80 cm wide?
* I need a small desk.
* Will this product fit in my space?
* Show me products with specific dimensions.

### Required information

* Maximum width
* Maximum depth
* Maximum height
* Measurement unit
* Product category

### Resolution

Filter only products with verified dimensions.

When dimensions are unavailable:

> I could not find verified dimensions for this product. I can ask our support team to confirm them before you order.

### Escalation

Escalate when product dimensions are missing or when installation access may be difficult.

---

## `PD-006` — Product recommendation

### Possible questions

* Which sofa do you recommend?
* What is the best table for a family of six?
* Help me choose a mattress.
* Which office chair should I buy?
* I do not know which product is suitable.
* Can you recommend something for a small space?

### Required information

* Intended use
* Room
* Number of users
* Budget
* Style
* Dimensions
* Desired material
* Delivery city

### Resolution

The bot should recommend a small number of verified products and explain the selection criteria.

It must not say that one product is “the best” without specifying why.

### Escalation

Escalate when:

* Requirements are complex.
* The product has technical constraints.
* The customer needs custom design.
* The order value is high.

---

## `PD-007` — Find matching products

### Possible questions

* Which chairs match this table?
* What rug goes with this sofa?
* Which lamp matches this collection?
* Show me products with the same style.
* What can I add to complete this room?
* Do you have matching furniture?

### Required information

* Current product
* Desired complementary product
* Preferred style or color
* Budget

### Resolution

Use:

* Product collections
* Curated relationships
* Shared attributes
* Verified compatibility

The chatbot must not invent product compatibility based only on images.

---

# 5. Product information cases

## `PI-001` — Product dimensions

### Possible questions

* What are the dimensions?
* How wide is this sofa?
* What is the height of this table?
* Will it fit through my door?
* What is the packaged size?
* How much space does it require?

### Required data

* Product ID
* Variant ID when applicable

### Resolution

Return verified:

* Width
* Height
* Depth
* Weight
* Package dimensions
* Recommended clearance

### Escalation

Missing or conflicting dimensions require human confirmation.

---

## `PI-002` — Materials and composition

### Possible questions

* What is this made of?
* Is this real wood?
* What type of fabric is used?
* Is the frame made of metal?
* Is this marble or an imitation?
* What is inside the mattress?

### Resolution

Return verified product specifications.

Never infer materials from:

* Images
* Product title
* Price
* Similar products

### Escalation

Escalate when supplier information is incomplete.

---

## `PI-003` — Colors, sizes, and variants

### Possible questions

* Is this available in black?
* Do you have another size?
* Which colors are available?
* Can I change the fabric?
* Is there a larger version?
* I cannot select the color I want.

### Resolution

Display:

* Existing variants
* Available variants
* Out-of-stock variants
* Price differences
* Variant images when available

### Escalation

Customization requests should be sent to sales or design support.

---

## `PI-004` — Stock availability

### Possible questions

* Is this product available?
* How many are left?
* When will it be back in stock?
* Can I pre-order it?
* Why can I not add it to my cart?
* Is the selected color available?

### Resolution

Return the authoritative stock state:

* Available
* Low stock
* Out of stock
* Pre-order
* Made to order
* Status unavailable

### Escalation

Escalate when stock information conflicts between the product page and checkout.

---

## `PI-005` — Product delivery estimate

### Possible questions

* How long will delivery take?
* When can I receive this product?
* Is this product delivered immediately?
* Why does this item take longer?
* Can I receive it before a specific date?

### Required information

* Product
* Variant
* Quantity
* Delivery city
* Current stock
* Fulfilment source

### Resolution

Provide an estimated range based on verified rules.

Never guarantee a date unless a confirmed appointment exists.

---

## `PI-006` — Assembly and installation

### Possible questions

* Does this require assembly?
* Is installation included?
* Will someone assemble it?
* What tools do I need?
* Can I install it myself?
* How much does installation cost?

### Resolution

Return verified assembly information.

Possible answers:

* Fully assembled
* Partial assembly required
* Professional installation recommended
* Installation included
* Installation available separately

### Escalation

Scheduling or quotation requests go to a human agent.

---

## `PI-007` — Product care and maintenance

### Possible questions

* How do I clean this?
* Can I use water on this material?
* How do I protect the wood?
* Can this product stay outdoors?
* How should I clean the fabric?
* What products should I avoid?

### Resolution

Provide only approved maintenance instructions for the specific material.

### Escalation

Damage caused by cleaning should be evaluated as an after-sales case.

---

## `PI-008` — Warranty information

### Possible questions

* Does this have a warranty?
* How long is the warranty?
* What does the warranty cover?
* Is damage during delivery covered?
* How do I submit a warranty claim?
* Is normal wear covered?

### Resolution

Return:

* Warranty duration
* Covered problems
* Exclusions
* Claim process
* Required documents

Use configuration values rather than hard-coded assumptions.

---

## `PI-009` — Product customization

### Possible questions

* Can you make this in another size?
* Can I select another material?
* Can you create a custom color?
* Can this be made for my project?
* I need a large quantity with modifications.
* Can you personalize this product?

### Required information

* Product
* Requested change
* Dimensions
* Material
* Color
* Quantity
* Budget
* Deadline
* Delivery city

### Resolution

Collect the request and create a qualified lead.

### Escalation

Always transfer to a sales or design agent.

---

# 6. Account and authentication cases

## `AC-001` — Cannot create an account

### Possible questions

* Why can I not register?
* The registration form is not working.
* I receive an error when creating an account.
* My information is rejected.
* The Sign Up button does nothing.

### Required information

* Error message
* Email or phone
* Registration method
* Device and browser when relevant

### Resolution

Identify common causes:

* Missing field
* Invalid email
* Weak password
* Existing account
* Terms not accepted
* Temporary technical problem

### Escalation

Escalate unexplained errors or repeated failures.

---

## `AC-002` — Email or phone already exists

### Possible questions

* This email is already registered.
* My phone number is already used.
* I never created this account.
* How can I recover the existing account?
* Why can I not use my email?

### Resolution

Offer:

* Login
* Password reset
* OTP login
* Social login
* Ownership verification

Never reveal private information about the existing account.

---

## `AC-003` — OTP not received

### Possible questions

* I did not receive the code.
* The SMS never arrived.
* Can you resend the OTP?
* My login code is missing.
* I entered the right number but received nothing.

### Resolution steps

1. Confirm phone format and country code.
2. Check whether resend is allowed.
3. Ask the customer to wait briefly.
4. Confirm mobile network availability.
5. Offer another approved login option.

### Escalation

Escalate after the configured resend or retry threshold.

---

## `AC-004` — OTP expired or rejected

### Possible questions

* The code expired.
* My OTP is invalid.
* The code is not accepted.
* I received several codes.
* Which OTP should I use?

### Resolution

* Use the latest OTP.
* Request a new code.
* Explain that older codes become invalid.
* Prevent unlimited resends.

---

## `AC-005` — Forgotten password

### Possible questions

* I forgot my password.
* How can I reset my password?
* I cannot log in.
* My password is not accepted.
* Send me a password reset link.

### Resolution

Guide the customer through the approved reset process.

The chatbot must never ask for:

* Current password
* Previous password
* Email password
* Verification codes sent by a bank

---

## `AC-006` — Password reset email missing

### Possible questions

* I did not receive the reset email.
* The password link never arrived.
* I checked my inbox but found nothing.
* Can you resend the reset link?

### Resolution

* Confirm email spelling.
* Check spam or promotions folders.
* Check resend limits.
* Determine whether the account uses social login.
* Resend when authorized.

---

## `AC-007` — Google or Apple login problem

### Possible questions

* Google login is not working.
* Apple Sign-in failed.
* My social account is linked to another email.
* The login window closed.
* I get an authorization error.

### Resolution

Identify:

* Provider failure
* Cancelled authorization
* Email conflict
* Existing local account
* Missing provider data
* Temporary technical issue

### Escalation

Repeated provider errors should be sent to technical support.

---

## `AC-008` — Account blocked or suspended

### Possible questions

* Why is my account blocked?
* I cannot access my account.
* My account was suspended.
* I receive a verification message.
* How can I reactivate my account?

### Resolution

The chatbot should not reveal internal risk rules.

Customer response:

> Your account requires additional verification. I’ll transfer this request to the appropriate support team.

### Priority

High when unauthorized access is suspected.

---

## `AC-009` — Update account details

### Possible questions

* How do I change my phone number?
* I want to update my email.
* How can I change my name?
* I need to edit my delivery address.
* How do I change the language?

### Resolution

Guide the customer to the appropriate profile section.

Sensitive updates may require:

* Password confirmation
* OTP verification
* Re-authentication

---

## `AC-010` — Delete account

### Possible questions

* I want to delete my account.
* Remove all my data.
* How do I close my account?
* I no longer want to use Mayush.
* Can you erase my information?

### Resolution

Explain the approved deletion-request process.

### Escalation

Transfer to privacy or account administration.

The bot should not immediately delete the account.

---

## `AC-011` — Suspected account compromise

### Possible questions

* Someone accessed my account.
* I see an order I did not make.
* My password was changed.
* My email was changed.
* I received a login notification I do not recognize.

### Immediate response

1. Advise password reset.
2. Recommend securing the connected email account.
3. End other sessions when supported.
4. Escalate immediately.
5. Avoid exposing additional account data.

### Priority

Critical.

---

# 7. Cart, promotion, and checkout cases

## `CK-001` — Cannot add a product to cart

### Possible questions

* Add to cart is not working.
* Why can I not add this item?
* The product does not appear in my cart.
* The button does nothing.
* The selected quantity is rejected.

### Possible causes

* Out of stock
* Invalid variant
* Quantity exceeds stock
* Product disabled
* Session problem
* Technical error

### Resolution

Validate product, variant, quantity, and session.

---

## `CK-002` — Product disappeared from cart

### Possible questions

* My product disappeared.
* My cart is empty.
* I added an item yesterday and it is gone.
* Why was the product removed?
* My cart changed after login.

### Possible causes

* Product became unavailable
* Variant disabled
* Guest cart expired
* Cart synchronized after login
* Price or seller configuration changed

### Escalation

Escalate unexplained cart corruption or repeated loss.

---

## `CK-003` — Incorrect cart quantity

### Possible questions

* The quantity changes automatically.
* I cannot reduce the quantity.
* The cart contains two instead of one.
* Why can I not order more units?
* The stock quantity is incorrect.

### Resolution

Check:

* Available stock
* Minimum quantity
* Maximum quantity
* Purchase limits
* Variant stock

---

## `CK-004` — Product price changed

### Possible questions

* Why did the price increase?
* The cart price is different.
* The product page shows another price.
* My total changed.
* The price changed after selecting a color.

### Resolution

Compare:

* Base price
* Variant price
* Promotion
* Promotion expiration
* Quantity
* Delivery
* Tax
* Seller update

### Escalation

Escalate unexplained differences.

---

## `CK-005` — Coupon or promotion rejected

### Possible questions

* My coupon does not work.
* Why is my discount rejected?
* The promotion was not applied.
* It says the code is invalid.
* Can I use two coupons?

### Possible causes

* Expired
* Usage limit reached
* Minimum amount not met
* Customer not eligible
* Product excluded
* Category excluded
* Already used
* Incorrect code

### Resolution

Display only an approved customer-facing reason.

---

## `CK-006` — Unexpected delivery cost

### Possible questions

* Why is delivery so expensive?
* The delivery fee changed.
* I thought delivery was free.
* Why do I pay several delivery fees?
* Is delivery calculated per seller?

### Resolution

Explain the verified calculation based on:

* Destination
* Product dimensions
* Weight
* Seller
* Shipment count
* Free-delivery threshold
* Installation or handling

---

## `CK-007` — Address not accepted

### Possible questions

* My address is rejected.
* I cannot select my city.
* The postal code is invalid.
* The checkout does not accept my location.
* My saved address is missing.

### Resolution

Validate:

* Required fields
* Supported city
* Phone format
* Address length
* Postal code
* Delivery zone

---

## `CK-008` — Checkout page error

### Possible questions

* Checkout is not loading.
* I cannot continue to payment.
* The Continue button is not working.
* The page shows an error.
* Checkout returns me to the cart.

### Required information

* Checkout step
* Error message
* Device
* Browser
* Time
* Cart context

### Important rule

Do not ask the customer to repeatedly submit payment when an order or payment attempt may already exist.

---

## `CK-009` — Express Buy failed

### Possible questions

* Buy Now does not work.
* Express Buy gives an error.
* I cannot complete a direct purchase.
* The order was not created.
* I clicked Buy Now several times.

### Resolution

Check:

* Authentication
* Address
* Stock
* Variant
* Checkout lock
* Existing order
* Existing payment attempt

### Escalation

Escalate when duplicate or partial order creation is possible.

---

# 8. Payment cases

## Global payment warning

The chatbot must say when relevant:

> For your security, never share your full card number, CVV, banking password, or verification code in this chat.

---

## `PY-001` — Payment declined

### Possible questions

* My card was declined.
* Why did the payment fail?
* The bank rejected the transaction.
* I cannot pay.
* CMI says the payment was unsuccessful.

### Required information

* Order reference
* Payment method
* Approximate time
* Customer-facing error

### Resolution

* Verify whether a charge exists.
* Confirm online-payment activation.
* Recommend checking bank limits.
* Offer another approved payment method.
* Avoid repeated attempts when status is uncertain.

---

## `PY-002` — Payment pending

### Possible questions

* My payment is still pending.
* How long does verification take?
* Should I try again?
* The money is blocked but the order is pending.
* My order says awaiting payment.

### Resolution

Retrieve the real payment and order state.

### Important rule

Do not recommend a second payment while the first one may still be processing.

---

## `PY-003` — Payment successful but order missing

### Possible questions

* I was charged but there is no order.
* The bank accepted the payment, but Mayush shows nothing.
* I paid but did not receive confirmation.
* The amount was deducted without an order.
* My card was charged, but my cart is still full.

### Required information

* Customer identity
* Approximate amount
* Approximate time
* Email or phone
* Available payment reference
* Recent order search

### Bot action

1. Search recent orders.
2. Check payment ledger safely.
3. Check delayed order creation.
4. Tell the customer not to pay again.
5. Escalate.

### Escalation

Always.

### Priority

High.

---

## `PY-004` — Duplicate charge

### Possible questions

* I was charged twice.
* There are two payments for one order.
* Why do I see two bank transactions?
* I clicked payment once but was debited twice.
* I have duplicate payment records.

### Resolution

Collect safe transaction context without requesting full card details.

### Escalation

Always to payment support.

### Priority

High.

---

## `PY-005` — Payment page does not load

### Possible questions

* CMI is not opening.
* The payment page is blank.
* I cannot access the card form.
* The payment page keeps loading.
* The browser blocks the payment window.

### Resolution

* Verify the payment attempt state.
* Check browser compatibility.
* Check pop-up or redirect blocking.
* Offer safe retry only when no charge or active attempt exists.

---

## `PY-006` — Returned from payment without confirmation

### Possible questions

* I returned from CMI but saw no confirmation.
* The payment page closed.
* I was redirected to the cart.
* I do not know whether the payment succeeded.
* The success page did not appear.

### Resolution

Check both payment and order status before advising the customer.

### Escalation

Escalate conflicting or unavailable statuses.

---

## `PY-007` — Incorrect payment amount

### Possible questions

* Why was I charged more?
* The payment total is incorrect.
* My bank amount differs from the order.
* The discount disappeared at payment.
* The delivery cost was added twice.

### Resolution

Provide a verified breakdown:

* Products
* Quantities
* Discounts
* Delivery
* Tax
* Other approved fees

### Escalation

Escalate mismatches.

---

## `PY-008` — Change payment method

### Possible questions

* Can I pay another way?
* I want to use another card.
* Can I change from card to cash?
* Can I pay by bank transfer?
* What payment methods do you accept?

### Resolution

Display only currently supported methods.

Changing the method after order creation must follow approved business rules.

---

## `PY-009` — Payment receipt or invoice

### Possible questions

* Where is my invoice?
* Can I download a receipt?
* I need an invoice for my company.
* How do I change billing information?
* Send me proof of payment.

### Resolution

Provide access to the existing receipt or invoice when authorized.

### Escalation

Transfer corrected company invoices or legal billing changes to support.

---

## `PY-010` — Unknown or fraudulent charge

### Possible questions

* I do not recognize this Mayush payment.
* Someone used my card.
* I did not place this order.
* This transaction is fraudulent.
* My account contains an unknown payment.

### Immediate response

* Advise the customer to contact the bank.
* Secure the Mayush account.
* Do not request card details.
* Escalate to security and payment support.

### Priority

Critical.

---

# 9. Order cases

## `OR-001` — Order confirmation missing

### Possible questions

* I did not receive an order confirmation.
* Where is my confirmation email?
* Was my order created?
* I completed checkout but received nothing.
* Can you resend my confirmation?

### Resolution

* Search recent orders.
* Check order state.
* Verify notification delivery.
* Resend confirmation when allowed.

### Escalation

Missing order after payment becomes `PY-003`.

---

## `OR-002` — Order status inquiry

### Possible questions

* Where is my order?
* What is my order status?
* Is my order confirmed?
* Has my order shipped?
* Why is it still processing?

### Resolution

Translate internal states into clear customer-facing statuses.

Example:

```text
Awaiting payment
Payment verification
Confirmed
Preparing
Partially shipped
Shipped
Delivered
Cancelled
Refund processing
```

---

## `OR-003` — Order not visible in account

### Possible questions

* My order is missing.
* I cannot see my purchase.
* My previous orders disappeared.
* I ordered with another email.
* I bought as a guest.

### Possible causes

* Guest checkout
* Different account
* Different email
* Delayed order creation
* Incorrect login method

### Security rule

Verify ownership before showing order information.

---

## `OR-004` — Change delivery address

### Possible questions

* Can I change my address?
* I entered the wrong address.
* I moved after ordering.
* Can you deliver to another city?
* Change the delivery phone number.

### Resolution by order stage

* Before preparation: may be possible.
* During preparation: manual approval.
* After shipment: normally not guaranteed.

### Escalation

Escalate when fulfilment has started.

---

## `OR-005` — Change product, quantity, or variant

### Possible questions

* Can I change the color?
* I ordered the wrong size.
* I need two instead of one.
* Can I replace one product?
* I selected the wrong model.

### Resolution

Check:

* Order status
* Stock
* Price difference
* Fulfilment progress
* Seller approval requirements

### Escalation

Confirmed orders normally require human validation.

---

## `OR-006` — Cancel order

### Possible questions

* I want to cancel my order.
* Can I stop the delivery?
* I ordered by mistake.
* How do I cancel one item?
* Can I cancel after payment?

### Resolution

Check:

* Payment status
* Preparation state
* Shipment state
* Product type
* Cancellation eligibility

### Possible outcomes

* Cancellation available
* Manual review required
* Too late to cancel
* Return required after delivery

---

## `OR-007` — Partial shipment

### Possible questions

* Why did I receive only part of my order?
* My products are arriving separately.
* One item shipped, but another did not.
* Why are there several deliveries?
* Is part of my order missing?

### Resolution

Explain verified split-shipment reasons:

* Different vendors
* Different warehouses
* Different preparation times
* Large-item delivery
* Backordered product

---

## `OR-008` — Missing item from order

### Possible questions

* One item is missing.
* I ordered three but received two.
* Part of the package is missing.
* The invoice contains a product I did not receive.
* The delivery was incomplete.

### Required information

* Order
* Shipment
* Product
* Expected quantity
* Received quantity
* Package condition

### Escalation

Always requires investigation.

---

## `OR-009` — Guest order lookup

### Possible questions

* How do I track an order without an account?
* I ordered as a guest.
* I cannot see my guest order.
* How can I link the order to my account?
* I lost the guest order link.

### Verification

Use an approved combination such as:

* Order reference
* Email or phone
* Signed token
* Secure verification code

Never use the order number alone.

---

# 10. Delivery cases

## `DL-001` — Delivery availability by area

### Possible questions

* Do you deliver to my city?
* Is delivery available outside Casablanca?
* Can you deliver to Marrakech?
* Do you ship internationally?
* Which areas do you cover?

### Required information

* City
* Region
* Postal code when relevant
* Product when delivery depends on size

### Resolution

Return:

* Available
* Unavailable
* Manual quotation required
* Special conditions

---

## `DL-002` — Delivery price

### Possible questions

* How much is delivery?
* Is delivery free?
* Why do large products cost more?
* Can I know the delivery fee before checkout?
* Is delivery charged per product?

### Resolution

Use verified delivery rules.

Never invent a price.

---

## `DL-003` — Delivery estimate

### Possible questions

* When will my order arrive?
* How many days does delivery take?
* Can I receive it this week?
* What is the estimated date?
* Why does delivery take so long?

### Required data

* Order
* Product readiness
* Destination
* Fulfilment source
* Carrier status

### Resolution

Provide an estimated range unless an appointment is confirmed.

---

## `DL-004` — Delivery delay

### Possible questions

* My order is late.
* The delivery date has passed.
* Why has the order not arrived?
* I have been waiting too long.
* There is no delivery update.

### Resolution

1. Compare promised range with current date.
2. Check shipment status.
3. Provide latest verified information.
4. Escalate significant or unexplained delays.

---

## `DL-005` — Tracking unavailable

### Possible questions

* Where is the tracking number?
* The tracking link does not work.
* I cannot follow the shipment.
* The carrier shows no information.
* My order is shipped but not trackable.

### Possible explanations

* Not yet collected
* Tracking synchronization delay
* Internal delivery
* Vendor delivery
* Carrier issue

---

## `DL-006` — Customer absent during delivery

### Possible questions

* I missed the delivery.
* The driver came while I was away.
* How can I reschedule?
* Will the courier return?
* Where can I collect the package?

### Resolution

Provide verified:

* Redelivery process
* Pickup process
* Contact method
* Possible fees
* Maximum holding period

---

## `DL-007` — Courier did not contact customer

### Possible questions

* The driver never called me.
* Delivery failed without contact.
* My phone was available.
* The courier marked me absent.
* No one tried to reach me.

### Required information

* Order
* Delivery date
* Confirmed phone number
* Carrier status

### Escalation

Escalate after verifying the delivery attempt.

---

## `DL-008` — Marked delivered but not received

### Possible questions

* It says delivered, but I received nothing.
* Someone marked my order as delivered.
* The package is missing.
* I did not sign for the delivery.
* The order was delivered to the wrong person.

### Bot actions

1. Confirm delivery address.
2. Ask whether family, reception, or security received it.
3. Check proof of delivery.
4. Escalate immediately.

### Priority

High.

---

## `DL-009` — Damaged package

### Possible questions

* The package arrived damaged.
* The box is broken.
* The furniture was damaged during transport.
* Should I accept the delivery?
* The packaging was open.

### Required information

* Order
* Product
* Delivery date
* Package condition
* Photos
* Whether delivery was accepted

### Escalation

Claims support.

---

## `DL-010` — Building access or large-item constraint

### Possible questions

* There is no elevator.
* The sofa may not fit through the door.
* I live on the fourth floor.
* Can the delivery team use the stairs?
* The street is difficult to access.

### Required information

* Floor
* Elevator
* Stair width
* Door dimensions
* Parking access
* Product dimensions

### Resolution

Collect logistics information and escalate special-delivery planning when needed.

---

## `DL-011` — Installation request

### Possible questions

* Can you install the product?
* Is assembly included?
* Can I schedule installation?
* How much does installation cost?
* Will the delivery team assemble it?

### Resolution

Return approved service availability.

### Escalation

Appointments and custom quotations require a human agent.

---

# 11. Return, exchange, and refund cases

## `RT-001` — Return eligibility

### Possible questions

* Can I return this product?
* How many days do I have?
* Is an assembled item returnable?
* Can I return a customized product?
* Do I need the original packaging?

### Required information

* Order
* Product
* Delivery date
* Condition
* Category
* Assembly status
* Packaging
* Customization state

### Resolution

Evaluate against the configured return policy.

The chatbot must not override official policy.

---

## `RT-002` — Start a return request

### Possible questions

* I want to return an item.
* How do I create a return?
* The product is not suitable.
* I changed my mind.
* Where can I submit a return request?

### Required information

* Order
* Product
* Quantity
* Reason
* Condition
* Photos when required

### Resolution

Create or guide the approved return-request process.

---

## `RT-003` — Exchange request

### Possible questions

* Can I exchange the color?
* I need another size.
* I want a different model.
* Can I exchange instead of refund?
* The selected variant is wrong.

### Checks

* Product condition
* Exchange window
* Requested stock
* Price difference
* Delivery cost
* Product eligibility

### Escalation

Manual approval when payment adjustment or logistics are required.

---

## `RT-004` — Wrong product received

### Possible questions

* I received the wrong item.
* The color is different from my order.
* I received another model.
* The package belongs to someone else.
* The product reference is incorrect.

### Required information

* Order
* Ordered item
* Received item
* Photos
* Packaging label
* Delivery date

### Priority

High.

---

## `RT-005` — Damaged product

### Possible questions

* The product arrived broken.
* There is a scratch.
* One part is damaged.
* The glass is cracked.
* The product was damaged during delivery.

### Required information

* Product
* Damage type
* Delivery date
* Packaging condition
* Photos
* Assembly state

### Escalation

Always requires validation.

---

## `RT-006` — Defective product

### Possible questions

* The product does not work.
* The mechanism is defective.
* The chair is unstable.
* A part stopped working.
* The product has a manufacturing problem.

### Diagnosis categories

* Delivery damage
* Manufacturing defect
* Assembly problem
* Missing component
* Normal wear
* Misuse

### Escalation

Warranty or after-sales team.

---

## `RT-007` — Product does not match description

### Possible questions

* The product is not as described.
* The material looks different.
* The dimensions are wrong.
* The color is not like the image.
* A feature mentioned online is missing.

### Required information

* Product page
* Expected specification
* Received specification
* Photos
* Order

### Escalation

After-sales and catalog-quality teams.

---

## `RT-008` — Refund status

### Possible questions

* Where is my refund?
* Has my refund been approved?
* When will I receive the money?
* The return was accepted, but I received nothing.
* What is the refund status?

### Possible statuses

* Not requested
* Under review
* Approved
* Processing
* Sent
* Failed
* Completed

### Resolution

Provide the real status and approved processing guidance.

---

## `RT-009` — Refund delay

### Possible questions

* My refund is late.
* The expected period has passed.
* The bank has not received the refund.
* It says refunded, but I see nothing.
* Why is the refund taking so long?

### Checks

* Approval date
* Processing date
* Payment method
* Refund reference
* Expected processing period
* Failure state

### Escalation

Escalate after the approved processing period or when the status conflicts.

---

## `RT-010` — Return or refund rejected

### Possible questions

* Why was my return refused?
* I disagree with the decision.
* My refund was rejected.
* Can someone review my case?
* The product is defective, but the request was denied.

### Resolution

Display the authorized rejection reason.

### Escalation

Offer human review when the customer disputes the decision.

---

# 12. Interior design and professional-service cases

## `PR-001` — Interior design consultation

### Possible questions

* I need help designing my home.
* Can I speak with an interior designer?
* Do you provide design services?
* I want a professional opinion.
* Can you help me choose furniture for my project?

### Required information

* Project type
* City
* Area
* Rooms
* Style
* Budget
* Timeline
* Contact preference

### Resolution

Collect and qualify the lead.

### Escalation

Always transfer to the design team.

---

## `PR-002` — Complete interior project

### Possible questions

* Can you manage my entire interior project?
* I need a turnkey solution.
* Can you design and execute the works?
* I have a villa to furnish.
* I need an office or restaurant project.

### Required information

* Residential or professional project
* Surface area
* City
* Current project stage
* Scope
* Budget
* Deadline

### Escalation

High-value qualified lead.

---

## `PR-003` — Bulk or professional order

### Possible questions

* I need 50 chairs.
* Do you offer professional pricing?
* I need furniture for a hotel.
* Can I receive a bulk quotation?
* We want to purchase for several offices.

### Required information

* Company
* Product
* Quantity
* City
* Required date
* Billing needs
* Contact information

### Escalation

Sales support.

---

## `PR-004` — Professional account

### Possible questions

* Do you have accounts for designers?
* Can architects receive special conditions?
* I am a contractor.
* How can my company register?
* Do you offer professional benefits?

### Resolution

Explain approved benefits and application requirements.

---

## `PR-005` — Vendor registration

### Possible questions

* How can I sell on Mayush?
* I want to become a vendor.
* Can my brand join the marketplace?
* What documents are required?
* How do sellers register?

### Required information

* Company
* Product categories
* City
* Website or catalog
* Contact details
* Legal status

### Escalation

Vendor onboarding.

---

## `PR-006` — Custom product request

### Possible questions

* Can you manufacture a custom sofa?
* I need special dimensions.
* Can this product be personalized?
* I want a specific material.
* Can you reproduce a reference design?

### Required information

* Product type
* Dimensions
* Material
* Color
* Quantity
* Budget
* Deadline
* Reference images

### Escalation

Always to sales or design.

---

# 13. Technical support cases

## `TC-001` — Page not loading

### Possible questions

* The page does not open.
* The website keeps loading.
* I receive a connection error.
* The product page is unavailable.
* Mayush is not working.

### Required information

* Page
* Device
* Browser
* Connection
* Time
* Error message

### Resolution

Offer limited safe troubleshooting and record the technical context.

---

## `TC-002` — Blank page

### Possible questions

* I see a white screen.
* The page is blank.
* Nothing appears after login.
* Checkout is empty.
* The page loads without content.

### Resolution

* Refresh once.
* Confirm browser.
* Confirm device.
* Try another approved browser when appropriate.
* Escalate repeated cases.

---

## `TC-003` — Search problem

### Possible questions

* Search gives no results.
* I cannot find a product.
* Filters do not work.
* Search shows unrelated products.
* The search button does nothing.

### Required information

* Search phrase
* Filters
* Expected result
* Actual result
* Device

### Analytics

Tag for search-quality analysis.

---

## `TC-004` — Product image missing

### Possible questions

* The product image is not loading.
* I cannot see all photos.
* The image is broken.
* The selected color has no image.
* The gallery does not work.

### Resolution

Provide text specifications while technical support investigates.

Never invent visual details.

---

## `TC-005` — Button or form not working

### Possible questions

* The button does nothing.
* I cannot submit the form.
* Save is not working.
* I cannot continue.
* The page does not respond.

### Required information

* Button or form
* Page
* Expected action
* Actual result
* Error
* Device and browser

---

## `TC-006` — Mobile display problem

### Possible questions

* The page is broken on my phone.
* Text overlaps.
* The menu does not open.
* The button is outside the screen.
* The mobile layout is incorrect.

### Required information

* Device
* Operating system
* Browser
* Orientation
* Page
* Screenshot

---

## `TC-007` — Website is slow

### Possible questions

* The site is very slow.
* Product pages take too long.
* Checkout freezes.
* Images load slowly.
* The app or website is lagging.

### Resolution

Collect performance context without immediately blaming the customer’s network.

---

## `TC-008` — Unexpected error message

### Possible questions

* I receive an unknown error.
* Something went wrong.
* I see error 500.
* The action failed.
* The website tells me to try later.

### Required information

* Exact error
* Page
* Previous action
* Time
* Device
* Browser

### Escalation

Create a structured incident summary.

---

## `TC-009` — Live Chat problem

### Possible questions

* My message was not sent.
* The chat keeps reconnecting.
* I cannot open the chat.
* The agent’s answer is missing.
* Messages appear twice.
* Notifications do not work.

### Resolution

Check:

* Connection
* Conversation status
* Duplicate message ID
* Agent assignment
* WebSocket or polling fallback
* Notification state

### Important requirement

The system must provide another support channel when Live Chat itself is unavailable.

---

# 14. Complaint cases

## `CP-001` — Customer-service complaint

### Possible questions

* I am unhappy with the service.
* The agent did not help me.
* I want to make a complaint.
* Support was disrespectful.
* Nobody responds to me.

### Required information

* Conversation or order
* Date
* Summary
* Desired outcome

### Escalation

Serious complaints go to a supervisor.

---

## `CP-002` — Vendor complaint

### Possible questions

* I have a problem with the seller.
* The vendor gave incorrect information.
* The seller is not responding.
* I received poor service from the vendor.
* I want to report a seller.

### Required information

* Vendor
* Product
* Order
* Incident
* Evidence

### Escalation

Marketplace operations.

---

## `CP-003` — Delivery-agent complaint

### Possible questions

* The driver was disrespectful.
* The delivery agent did not follow instructions.
* The courier refused to deliver.
* The delivery person damaged my property.
* I want to report the driver.

### Required information

* Order
* Delivery date
* Incident
* Carrier
* Evidence when available

### Escalation

Logistics management.

---

## `CP-004` — Product authenticity concern

### Possible questions

* Is this product authentic?
* I think the product is counterfeit.
* The brand label looks suspicious.
* The product differs from the original.
* I want to report a fake product.

### Required information

* Product
* Seller
* Order
* Reason
* Photos

### Priority

High.

---

## `CP-005` — Formal escalation request

### Possible questions

* I want to speak with a manager.
* Escalate my complaint.
* I want a supervisor.
* I am not satisfied with the previous answer.
* I want a formal review.

### Resolution

Transfer without forcing the customer to repeat the issue.

---

# 15. Privacy and security cases

## `SC-001` — Personal-data access request

### Possible questions

* What information do you have about me?
* I want a copy of my data.
* Show me my stored personal information.
* How can I access my data?

### Resolution

Explain the approved privacy-request process.

### Escalation

Privacy team.

---

## `SC-002` — Personal-data correction

### Possible questions

* My information is incorrect.
* I need to correct my personal data.
* My name or email is wrong.
* How can I update legal information?

### Resolution

Separate normal profile updates from formal privacy corrections.

---

## `SC-003` — Personal-data deletion

### Possible questions

* Delete all my information.
* Remove my account and data.
* I want to be forgotten.
* Erase my personal data.

### Escalation

Privacy process.

The bot must not delete records automatically.

---

## `SC-004` — Data exposure report

### Possible questions

* I can see another customer’s information.
* Someone received my order information.
* My data may have been exposed.
* I found private information on the website.

### Immediate action

* Collect minimal details.
* Do not request public sharing of evidence.
* Escalate immediately to security.
* Preserve the report for investigation.

### Priority

Critical.

---

## `SC-005` — Vulnerability or security report

### Possible questions

* I found a security issue.
* There is a vulnerability.
* I can bypass access controls.
* I found an exposed API.
* How can I report a security problem?

### Resolution

Move the conversation to a restricted security channel.

Do not ask the reporter to post exploit details in general support chat.

---

## `SC-006` — Suspicious message or phishing

### Possible questions

* I received a suspicious Mayush email.
* Is this message real?
* Someone asked for my password.
* I received a strange payment link.
* Is this WhatsApp number official?

### Resolution

* Tell the customer not to click suspicious links.
* Never share passwords or OTPs.
* Verify only through approved Mayush channels.
* Escalate suspicious campaigns.

---

# 16. Human-support cases

## `HS-001` — Customer directly requests an agent

### Possible questions

* I want a human.
* Connect me to support.
* I do not want to talk to a bot.
* Let me speak with an agent.
* I need a real person.

### Resolution

Transfer immediately.

The customer should never be forced to continue with the bot.

---

## `HS-002` — Bot failed to understand

### Trigger conditions

* Two unrecognized messages
* Two incorrect category selections
* Repeated clarification failure
* Multiple equally likely intents

### Bot response

> I’m sorry, I could not understand your issue accurately enough. I’ll transfer the conversation to a support agent, and you will not need to repeat the information already provided.

---

## `HS-003` — Automated solution failed

### Trigger conditions

* First solution failed
* Deeper diagnostic failed
* Customer still needs help
* Same flow repeated twice

### Resolution

Transfer with the attempted steps included in the agent summary.

---

## `HS-004` — Customer frustration detected

### Possible phrases

* This is useless.
* You keep repeating yourself.
* I already explained.
* Nobody is helping me.
* This is unacceptable.
* I am tired of this.

### Bot response

> I understand that this situation is frustrating. I’m transferring the conversation to a support agent with the information already collected.

---

# 17. Universal conversation logic

Every issue should follow this logic:

```text
Customer message
      ↓
Detect language
      ↓
Detect category
      ↓
Detect issue case
      ↓
Check confidence
      ↓
Confirm issue when needed
      ↓
Collect required information
      ↓
Validate ownership and permissions
      ↓
Retrieve verified data
      ↓
Provide approved solution
      ↓
Ask: “Did this solve your problem?”
      ↓
 ┌───────────────┬──────────────────┐
 │ Yes           │ No               │
 ↓               ↓
Resolve       One deeper attempt
                  ↓
           Ask again if resolved
              /           \
            Yes            No
             ↓              ↓
          Resolve       Human agent
```

---

# 18. Escalation thresholds

Recommended initial rules:

| Rule                                  | Threshold |
| ------------------------------------- | --------: |
| Unrecognized messages                 |         2 |
| Incorrect intent detections           |         2 |
| Repeated flow cycles                  |         2 |
| Failed solutions                      |         2 |
| Maximum bot turns before reassessment |        12 |
| Customer asks for a human             | Immediate |
| Sensitive payment case                | Immediate |
| Privacy or security case              | Immediate |
| Fraud suspicion                       | Immediate |
| Missing verified information          |  Escalate |
| Internal service unavailable          |  Escalate |

---

# 19. Human handoff record

When transferring the conversation, create a structured handoff:

```text
Conversation ID
Customer type
Customer ID or guest identity
Customer name
Preferred language
Category
Issue case ID
Issue description
Detected intent confidence
Order reference
Product reference
Payment context
Delivery context
Information collected
Steps attempted
Solutions proposed
Customer responses
Escalation reason
Priority
Suggested department
Customer sentiment
Recommended next action
```

---

# 20. Example complete issue record

```text
Case ID:
PY-003

Category:
Payment

Case name:
Payment successful but order missing

Eligible users:
Guest buyer and authenticated buyer

Question variants:
- I paid, but there is no order.
- My card was charged without confirmation.
- The bank accepted the transaction, but I see nothing.
- I was charged, but my cart is still full.

Description:
The customer reports that payment appears successful externally, but Mayush does not show a matching confirmed order.

Required information:
- Customer identity
- Email or phone
- Approximate payment time
- Approximate amount
- Available order reference
- Safe payment reference

Validation:
- Verify customer ownership
- Never request a full card number
- Never request CVV
- Never request banking OTP

Data sources:
- Order service
- Payment ledger
- Checkout session
- Notification service

Resolution steps:
1. Search for recent matching orders.
2. Check the latest payment state.
3. Check whether order creation is delayed.
4. Check whether confirmation notification failed.
5. Tell the customer not to pay again.
6. Transfer to payment support.

Success condition:
A verified matching order is found and its status can safely be explained.

Failure condition:
Payment appears successful, but no matching order exists.

Escalation:
Always escalate when no matching order is found.

Priority:
High

Department:
Payment Support

Bot answer:
“I can see that this requires payment verification. Please do not make another payment while we investigate. I’m transferring your conversation to our payment support team with the details already collected.”

Analytics tags:
payment_issue
charged_no_order
checkout_reconciliation
high_priority
```

---

# 21. Recommended database relationships

```text
support_categories
    └── support_cases
            ├── case_question_variants
            ├── case_required_fields
            ├── case_validation_rules
            ├── case_resolution_steps
            ├── case_solutions
            ├── case_escalation_rules
            ├── case_translations
            ├── case_related_cases
            └── case_analytics_tags

chat_conversations
    ├── chat_messages
    ├── chatbot_sessions
    ├── chatbot_detected_intents
    ├── chatbot_collected_values
    ├── chatbot_resolution_attempts
    ├── chatbot_escalations
    ├── agent_assignments
    └── conversation_feedback
```

---

# 22. Suggested chatbot support tables

## `support_categories`

Stores main topics.

```text
id
code
name
description
icon
display_order
status
```

## `support_cases`

Stores every issue.

```text
id
category_id
case_code
name
description
eligible_user_types
priority
department
status
version
```

## `case_question_variants`

Stores different customer formulations.

```text
id
case_id
language
question
keywords
weight
status
```

## `case_required_fields`

Defines information the bot must collect.

```text
id
case_id
field_key
label
field_type
required
validation_rule
display_order
```

## `case_resolution_steps`

Defines the controlled conversation steps.

```text
id
case_id
step_order
step_type
message_template
action_key
success_transition
failure_transition
```

## `case_solutions`

Stores approved solutions.

```text
id
case_id
solution_code
title
content
conditions
language
status
version
```

## `case_escalation_rules`

Stores transfer conditions.

```text
id
case_id
rule_type
operator
threshold
priority
target_department
handoff_message
status
```

## `chatbot_collected_values`

Stores answers collected during the conversation.

```text
id
conversation_id
case_id
field_key
field_value
is_sensitive
collected_at
```

## `chatbot_resolution_attempts`

Tracks automated solutions.

```text
id
conversation_id
case_id
solution_id
attempt_number
result
customer_confirmed
created_at
```

## `chatbot_escalations`

Tracks human transfers.

```text
id
conversation_id
case_id
reason
priority
target_department
summary
assigned_agent_id
escalated_at
accepted_at
```

---

# 23. Content-management requirements

Administrators should eventually be able to:

* Create new categories.
* Add new issue cases.
* Add question variants.
* Add translations.
* Edit troubleshooting steps.
* Activate or disable solutions.
* Change escalation limits.
* Define department ownership.
* Review failed conversations.
* Identify missing issue cases.
* See cases with high escalation rates.
* Version chatbot answers.
* Schedule knowledge reviews.

Chatbot logic should not be permanently hard-coded into one controller or frontend script.

---

# 24. Final result

This database should become the central support knowledge system for Mayush.

Each customer question will map to:

```text
Category
    ↓
Issue case
    ↓
Required information
    ↓
Verified data source
    ↓
Controlled resolution steps
    ↓
Success confirmation
    ↓
Resolution or human escalation
```

The most important next step is to convert these issue cases into a **technical flow-definition format**, such as structured JSON, database seed data, or an admin-managed flow engine.
