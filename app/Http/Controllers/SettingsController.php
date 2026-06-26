<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', BusinessSetting::class);
        return view('settings.index', [
            'settings' => BusinessSetting::instance(),
            'stores' => Store::with('category')->get(),
            'storeCategories' => StoreCategory::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', BusinessSetting::class);
        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15',
            'pan' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email',
            'currency' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:50',
            'invoice_prefix' => 'nullable|string|max:10',
            'terms' => 'nullable|string',
            'upi_id' => 'nullable|string|max:100',
            'payee_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:30',
            'ifsc' => 'nullable|string|max:15',
            'show_qr_on_invoice' => 'boolean',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = BusinessSetting::instance();
        if ($request->hasFile('logo')) {
            if ($settings->logo) Storage::disk('public')->delete($settings->logo);
            $data['logo'] = $request->file('logo')->store('business', 'public');
        }

        $settings->update($data);
        activity('settings')->log('Business settings updated');

        return back()->with('success', 'Settings saved.');
    }
}
