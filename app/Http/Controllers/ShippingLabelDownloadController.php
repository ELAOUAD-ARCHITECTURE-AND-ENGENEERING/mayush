<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PDF;
use ZipArchive;

class ShippingLabelDownloadController extends Controller
{
    public function shipping_label_download($id)
    {
        $order = $this->authorizedOrder($id);
        $font = $this->getFontAndDirection();
        $config = $this->getPresetConfig();

        return PDF::loadView($config['view'], $this->viewData($order, $font), [], $config['pdfConfig'])
            ->download('order-' . ($order->code ?? $order->id) . '.pdf');
    }

    public function shipping_label_printer($id)
    {
        $order = $this->authorizedOrder($id);
        $font = $this->getFontAndDirection();
        $config = $this->getPresetConfig();

        return PDF::loadView($config['view'], $this->viewData($order, $font), [], $config['pdfConfig'])
            ->stream('order-' . ($order->code ?? $order->id) . '.pdf');
    }

    public function shipping_label_print($id)
    {
        $order = $this->authorizedOrder($id);
        $font = $this->getFontAndDirection();
        $config = $this->getPresetConfig();

        $preset = $config['preset'];
        $labelSizes = [
            '2x3' => ['width' => '2in', 'height' => '3in'],
            '3x4' => ['width' => '3in', 'height' => '4in'],
            '4x4' => ['width' => '4in', 'height' => '4in'],
            '4x6' => ['width' => '4in', 'height' => '6in'],
        ];

        return view($config['view'], array_merge($this->viewData($order, $font), [
            'label_width' => $labelSizes[$preset]['width'] ?? '4in',
            'label_height' => $labelSizes[$preset]['height'] ?? '6in',
        ]));
    }

    public function bulk_shipping_label_download(Request $request)
    {
        $orders = $this->authorizedOrdersFromRequest($request);
        if ($orders->isEmpty()) {
            flash(translate('No valid orders found.'))->warning();
            return back();
        }

        $font = $this->getFontAndDirection();
        $config = $this->getPresetConfig();
        $tempDir = storage_path('app/temp_shipping_labels_' . auth()->id() . '_' . time());
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/shipping_labels.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            flash(translate('Could not create ZIP file. Please try again.'))->error();
            return back();
        }

        foreach ($orders as $order) {
            try {
                $zip->addFromString(
                    'order-' . ($order->code ?? $order->id) . '.pdf',
                    PDF::loadView($config['view'], $this->viewData($order, $font), [], $config['pdfConfig'])->output()
                );
            } catch (\Throwable $e) {
                Log::warning('Shipping label PDF generation failed', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $zip->close();

        return response()->download($zipPath, 'shipping_labels_' . now()->format('Ymd_His') . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function bulk_shipping_label_print(Request $request)
    {
        $orders = $this->authorizedOrdersFromRequest($request);
        if ($orders->isEmpty()) {
            flash(translate('No valid orders found.'))->warning();
            return back();
        }

        $font = $this->getFontAndDirection();
        $config = $this->getPresetConfig();
        $mpdf = new \Mpdf\Mpdf($config['pdfConfig']);

        foreach ($orders as $index => $order) {
            if ($index > 0) {
                $mpdf->AddPage();
            }

            $mpdf->WriteHTML(view($config['view'], $this->viewData($order, $font))->render());
        }

        return $mpdf->Output('bulk_shipping_labels.pdf', 'I');
    }

    private function authorizedOrder($id): Order
    {
        $order = Order::findOrFail($id);
        abort_unless($this->canAccessOrder($order), 403);

        return $order;
    }

    private function authorizedOrdersFromRequest(Request $request)
    {
        $orderIds = collect($request->input('order_ids', []))->filter()->values();
        if ($orderIds->isEmpty()) {
            return collect();
        }

        return Order::whereIn('id', $orderIds)
            ->get()
            ->filter(fn (Order $order) => $this->canAccessOrder($order))
            ->values();
    }

    private function canAccessOrder(Order $order): bool
    {
        $user = auth()->user();

        return $user && (
            in_array($user->user_type, ['admin', 'staff'], true)
            || (int) $user->id === (int) $order->user_id
            || (int) $user->id === (int) $order->seller_id
        );
    }

    private function getFontAndDirection(): array
    {
        $currencyCode = Session::get('currency_code')
            ?: optional(Currency::find(get_setting('system_default_currency')))->code
            ?: 'MAD';
        $languageCode = Session::get('locale', Config::get('app.locale'));
        $lang = Language::where('code', $languageCode)->first();
        $direction = $lang && (int) $lang->rtl === 1 ? 'rtl' : 'ltr';

        return [
            'currency_code' => $currencyCode,
            'language_code' => $languageCode,
            'direction' => $direction,
            'text_align' => $direction === 'rtl' ? 'right' : 'left',
            'not_text_align' => $direction === 'rtl' ? 'left' : 'right',
            'font_family' => $currencyCode === 'USD' ? "'Roboto','sans-serif'" : 'freeserif',
        ];
    }

    private function getPresetConfig(): array
    {
        $shippingLabel = json_decode(get_setting('shipping_label'), true) ?: [];
        $preset = $shippingLabel['label_size_preset'] ?? '4x6';
        $labelSizes = [
            '2x3' => ['width' => 57, 'height' => 85],
            '3x4' => ['width' => 85, 'height' => 113],
            '4x4' => ['width' => 113, 'height' => 113],
            '4x6' => ['width' => 113, 'height' => 170],
        ];

        $view = match ($preset) {
            '2x3' => 'backend.shipping_labels.shipping_label_mini',
            '3x4', '4x4' => 'backend.shipping_labels.shipping_label_small',
            default => 'backend.shipping_labels.shipping_label',
        };

        $size = $labelSizes[$preset] ?? $labelSizes['4x6'];

        return [
            'preset' => $preset,
            'view' => $view,
            'pdfConfig' => [
                'format' => [$size['width'], $size['height']],
                'orientation' => 'portrait',
            ],
        ];
    }

    private function viewData(Order $order, array $font): array
    {
        return [
            'order' => $order,
            'font_family' => $font['font_family'],
            'direction' => $font['direction'],
            'text_align' => $font['text_align'],
            'not_text_align' => $font['not_text_align'],
        ];
    }
}
