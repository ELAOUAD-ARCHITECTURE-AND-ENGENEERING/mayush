@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="align-items-center">
        <h1 class="h3">{{ translate('Loyalty Tier Configuration') }}</h1>
        <p class="text-muted">{{ translate('Configure automatic loyalty tiers based on customer annual spend.') }}</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">
            <i class="las la-star text-warning mr-1"></i>
            {{ translate('Loyalty Tiers') }}
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.loyalty.config.update') }}" method="POST">
            @csrf

            @if ($tiers->count() > 0)
                <div class="table-responsive">
                    <table class="table aiz-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ translate('Tier Name') }}</th>
                                <th>{{ translate('Level') }}</th>
                                <th>{{ translate('Min Annual Spend') }}</th>
                                <th>{{ translate('Point Multiplier') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tiers as $tier)
                                <tr>
                                    <td>
                                        <strong>{{ $tier->getTranslation('name') }}</strong>
                                    </td>
                                    <td>
                                        <select name="tiers[{{ $tier->id }}][tier_level]" class="form-control form-control-sm" style="width:120px;">
                                            <option value="0" {{ $tier->tier_level == 0 ? 'selected' : '' }}>0 — {{ translate('Basic') }}</option>
                                            <option value="1" {{ $tier->tier_level == 1 ? 'selected' : '' }}>1 — {{ translate('Silver') }}</option>
                                            <option value="2" {{ $tier->tier_level == 2 ? 'selected' : '' }}>2 — {{ translate('Gold') }}</option>
                                            <option value="3" {{ $tier->tier_level == 3 ? 'selected' : '' }}>3 — {{ translate('Platinum') }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="tiers[{{ $tier->id }}][min_spend]"
                                            value="{{ $tier->min_spend ?? 0 }}"
                                            class="form-control form-control-sm" style="width:150px;"
                                            placeholder="{{ translate('e.g. 5000') }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="tiers[{{ $tier->id }}][loyalty_multiplier]"
                                            value="{{ $tier->loyalty_multiplier ?? 1.0 }}"
                                            class="form-control form-control-sm" style="width:100px;"
                                            placeholder="1.5">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted mb-3">{{ translate('No loyalty tiers configured yet. Create one below.') }}</p>
                </div>
            @endif

            <hr>

            {{-- New Tier Form --}}
            <h6 class="fw-600 mb-3">
                <i class="las la-plus-circle mr-1"></i>
                {{ translate('Add New Loyalty Tier') }}
            </h6>
            <div class="row gutters-5">
                <div class="col-md-3">
                    <label class="fs-12">{{ translate('Tier Name') }}</label>
                    <input type="text" name="new_tier_name" class="form-control form-control-sm" placeholder="{{ translate('e.g. Silver') }}">
                </div>
                <div class="col-md-2">
                    <label class="fs-12">{{ translate('Level') }}</label>
                    <select name="new_tier_level" class="form-control form-control-sm">
                        <option value="1">1 — {{ translate('Silver') }}</option>
                        <option value="2">2 — {{ translate('Gold') }}</option>
                        <option value="3">3 — {{ translate('Platinum') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fs-12">{{ translate('Min Annual Spend') }}</label>
                    <input type="number" step="0.01" name="new_tier_min_spend" class="form-control form-control-sm" placeholder="5000">
                </div>
                <div class="col-md-2">
                    <label class="fs-12">{{ translate('Point Multiplier') }}</label>
                    <input type="number" step="0.01" name="new_tier_multiplier" class="form-control form-control-sm" value="1.5" placeholder="1.5">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="las la-save mr-1"></i>
                    {{ translate('Save Configuration') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- How it works --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">
            <i class="las la-info-circle mr-1"></i>
            {{ translate('How Automatic Tiers Work') }}
        </h5>
    </div>
    <div class="card-body">
        <ol class="mb-0">
            <li class="mb-2">{{ translate('Tiers with "is_loyalty_tier" are auto-assigned — they are not purchasable by customers.') }}</li>
            <li class="mb-2">{{ translate('When a customer completes a payment, the system checks their rolling 12-month spend.') }}</li>
            <li class="mb-2">{{ translate('If the spend meets or exceeds a tier\'s "Min Annual Spend", they are automatically upgraded.') }}</li>
            <li class="mb-2">{{ translate('The "Point Multiplier" amplifies club points earned on every future purchase (e.g. 1.5x = 50% bonus).') }}</li>
        </ol>
    </div>
</div>

@endsection
