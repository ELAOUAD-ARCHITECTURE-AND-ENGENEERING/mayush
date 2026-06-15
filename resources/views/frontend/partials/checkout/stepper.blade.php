@php
    $step = $step ?? 1;
    $failed = $failed ?? false;
@endphp

<section class="purchase-stepper-section pt-5 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="purchase-stepper-wrap">
                    <!-- Progress Track -->
                    <div class="stepper-track">
                        <div class="stepper-progress" style="width: {{ ($step - 1) * 25 }}%;"></div>
                    </div>

                    <!-- Steps -->
                    <div class="stepper-nodes d-flex justify-content-between align-items-start">
                        <!-- Step 1: Cart -->
                        <div class="stepper-node {{ $step > 1 ? 'completed' : ($step == 1 ? 'active' : 'pending') }}">
                            <div class="stepper-icon-wrap">
                                <i class="las la-shopping-cart"></i>
                            </div>
                            <h3 class="stepper-label d-none d-lg-block">1. {{ translate('My Cart') }}</h3>
                        </div>

                        <!-- Step 2: Shipping -->
                        <div class="stepper-node {{ $step > 2 ? 'completed' : ($step == 2 ? 'active' : 'pending') }}">
                            <div class="stepper-icon-wrap">
                                <i class="las la-map"></i>
                            </div>
                            <h3 class="stepper-label d-none d-lg-block">2. {{ translate('Shipping info') }}</h3>
                        </div>

                        <!-- Step 3: Delivery -->
                        <div class="stepper-node {{ $step > 3 ? 'completed' : ($step == 3 ? 'active' : 'pending') }}">
                            <div class="stepper-icon-wrap">
                                <i class="las la-truck"></i>
                            </div>
                            <h3 class="stepper-label d-none d-lg-block">3. {{ translate('Delivery info') }}</h3>
                        </div>

                        <!-- Step 4: Payment -->
                        <div class="stepper-node {{ $step > 4 ? 'completed' : ($step == 4 ? 'active' : 'pending') }}">
                            <div class="stepper-icon-wrap">
                                <i class="las la-credit-card"></i>
                            </div>
                            <h3 class="stepper-label d-none d-lg-block">4. {{ translate('Payment') }}</h3>
                        </div>

                        <!-- Step 5: Order Confirmation -->
                        <div class="stepper-node {{ $step == 5 ? ($failed ? 'active failed' : 'active completed') : 'pending' }}">
                            <div class="stepper-icon-wrap">
                                <i class="las {{ $failed ? 'la-times-circle' : 'la-check-circle' }}"></i>
                            </div>
                            <h3 class="stepper-label d-none d-lg-block">
                                5. {{ $failed ? translate('Failed') : translate('Confirmation') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    :root {
        --stepper-track-bg: #f1f2f4;
        --stepper-node-size: 50px;
        --stepper-icon-size: 24px;
        --stepper-font: var(--mayush-font-body);
    }

    .purchase-stepper-wrap {
        position: relative;
        padding-top: 10px;
        margin-bottom: 20px;
        font-family: var(--stepper-font);
    }

    /* Track line */
    .stepper-track {
        position: absolute;
        top: calc(var(--stepper-node-size) / 2 + 10px);
        left: 25px; /* Half of node size */
        right: 25px;
        height: 4px;
        background: var(--stepper-track-bg);
        border-radius: 10px;
        z-index: 0;
    }

    .stepper-progress {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: var(--primary);
        border-radius: 10px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 0 10px rgba(var(--primary-rgb, 212, 53, 51), 0.3);
    }

    /* Nodes */
    .stepper-nodes {
        position: relative;
        z-index: 1;
    }

    .stepper-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 120px;
        text-align: center;
    }

    .stepper-icon-wrap {
        width: var(--stepper-node-size);
        height: var(--stepper-node-size);
        background: white;
        border: 2px solid var(--stepper-track-bg);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        transition: all 0.3s ease;
        position: relative;
    }

    .stepper-icon-wrap i {
        font-size: var(--stepper-icon-size);
        color: #adb5bd;
        transition: all 0.3s ease;
    }

    .stepper-label {
        font-size: 14px;
        font-weight: 600;
        color: #adb5bd;
        margin: 0;
        transition: all 0.3s ease;
    }

    /* Active State */
    .stepper-node.active .stepper-icon-wrap {
        border-color: var(--primary);
        background: var(--primary);
        box-shadow: 0 0 15px rgba(212, 53, 51, 0.2);
        transform: scale(1.1);
    }

    .stepper-node.active .stepper-icon-wrap i {
        color: white;
    }

    .stepper-node.active .stepper-label {
        color: var(--dark);
    }

    /* Completed State */
    .stepper-node.completed .stepper-icon-wrap {
        border-color: var(--success);
        background: var(--success);
    }

    .stepper-node.completed .stepper-icon-wrap i {
        color: white;
    }

    .stepper-node.completed .stepper-label {
        color: var(--success);
    }

    /* Failed State */
    .stepper-node.active.failed .stepper-icon-wrap {
        border-color: var(--danger);
        background: var(--danger);
        box-shadow: 0 0 15px rgba(220, 53, 69, 0.2);
    }

    .stepper-node.active.failed .stepper-label {
        color: var(--danger);
    }

    /* Hover effect for past steps */
    .stepper-node.completed:hover .stepper-icon-wrap {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(var(--success-rgb, 133, 181, 103), 0.3);
    }

    @media (max-width: 991px) {
        .stepper-node {
            width: 60px;
        }
        .stepper-track {
            left: 30px;
            right: 30px;
        }
    }
</style>
