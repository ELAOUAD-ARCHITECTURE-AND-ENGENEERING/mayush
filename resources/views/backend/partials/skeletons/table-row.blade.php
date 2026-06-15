@php
    $cols = $cols ?? 6; // Default to 6 columns
@endphp
<tr>
    @for ($i = 0; $i < $cols; $i++)
        <td class="align-middle">
            @if($i == 0)
                <div class="skeleton-shimmer h-20px w-20px rounded"></div>
            @elseif($i == 1)
                <div class="skeleton-shimmer h-50px w-50px rounded"></div>
            @elseif($i == 2)
                <div class="skeleton-shimmer h-15px w-100 mb-2 rounded"></div>
                <div class="skeleton-shimmer h-10px w-50 rounded"></div>
            @else
                <div class="skeleton-shimmer h-12px w-75 rounded"></div>
            @endif
        </td>
    @endfor
</tr>
