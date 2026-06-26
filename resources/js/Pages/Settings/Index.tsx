import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function SettingsIndex({ settings, stores, storeCategories }: { settings: any; stores: any[]; storeCategories: any[] }) {
    const { data, setData, post, processing, errors } = useForm({
        business_name: settings.business_name ?? '', gstin: settings.gstin ?? '', pan: settings.pan ?? '',
        address: settings.address ?? '', phone: settings.phone ?? '', email: settings.email ?? '',
        currency: settings.currency ?? 'INR', timezone: settings.timezone ?? 'Asia/Kolkata',
        invoice_prefix: settings.invoice_prefix ?? 'INV', terms: settings.terms ?? '',
        upi_id: settings.upi_id ?? '', payee_name: settings.payee_name ?? '',
        bank_account: settings.bank_account ?? '', ifsc: settings.ifsc ?? '',
        show_qr_on_invoice: settings.show_qr_on_invoice ?? true, logo: null as any,
        _method: 'POST',
    });

    const inp = "w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500";

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('settings.update'));
    };

    const Section = ({ title, children }: { title: string; children: React.ReactNode }) => (
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
            <h3 className="font-semibold text-gray-700 dark:text-gray-300 border-b pb-2">{title}</h3>
            {children}
        </div>
    );

    const Field = ({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) => (
        <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{label}</label>
            {children}
            {error && <p className="text-red-500 text-xs mt-1">{error}</p>}
        </div>
    );

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">Business Settings</h2>}>
            <Head title="Settings" />
            <form onSubmit={submit} encType="multipart/form-data">
                <div className="py-6 px-4 max-w-3xl mx-auto space-y-6">
                    <Section title="Business Information">
                        <div className="grid grid-cols-2 gap-4">
                            <Field label="Business Name *" error={errors.business_name}>
                                <input className={inp} value={data.business_name} onChange={e => setData('business_name', e.target.value)} required />
                            </Field>
                            <Field label="GSTIN" error={errors.gstin}>
                                <input className={inp} value={data.gstin} onChange={e => setData('gstin', e.target.value)} maxLength={15} />
                            </Field>
                            <Field label="PAN" error={errors.pan}>
                                <input className={inp} value={data.pan} onChange={e => setData('pan', e.target.value)} maxLength={10} />
                            </Field>
                            <Field label="Phone" error={errors.phone}>
                                <input className={inp} value={data.phone} onChange={e => setData('phone', e.target.value)} />
                            </Field>
                            <Field label="Email" error={errors.email}>
                                <input type="email" className={inp} value={data.email} onChange={e => setData('email', e.target.value)} />
                            </Field>
                            <Field label="Invoice Prefix" error={errors.invoice_prefix}>
                                <input className={inp} value={data.invoice_prefix} onChange={e => setData('invoice_prefix', e.target.value)} maxLength={10} />
                            </Field>
                        </div>
                        <Field label="Address" error={errors.address}>
                            <textarea className={inp} rows={2} value={data.address} onChange={e => setData('address', e.target.value)} />
                        </Field>
                        <Field label="Terms & Conditions" error={errors.terms}>
                            <textarea className={inp} rows={2} value={data.terms} onChange={e => setData('terms', e.target.value)} />
                        </Field>
                        <Field label="Logo" error={errors.logo}>
                            <input type="file" accept="image/*" className={inp} onChange={e => setData('logo', e.target.files?.[0] as any)} />
                            {settings.logo && <img src={`/storage/${settings.logo}`} className="h-16 mt-2 rounded" alt="logo" />}
                        </Field>
                    </Section>

                    <Section title="Payment Settings">
                        <div className="grid grid-cols-2 gap-4">
                            <Field label="UPI ID" error={errors.upi_id}>
                                <input className={inp} value={data.upi_id} onChange={e => setData('upi_id', e.target.value)} placeholder="yourname@upi" />
                            </Field>
                            <Field label="Payee Name" error={errors.payee_name}>
                                <input className={inp} value={data.payee_name} onChange={e => setData('payee_name', e.target.value)} />
                            </Field>
                            <Field label="Bank Account No." error={errors.bank_account}>
                                <input className={inp} value={data.bank_account} onChange={e => setData('bank_account', e.target.value)} />
                            </Field>
                            <Field label="IFSC Code" error={errors.ifsc}>
                                <input className={inp} value={data.ifsc} onChange={e => setData('ifsc', e.target.value)} />
                            </Field>
                        </div>
                        <label className="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" checked={data.show_qr_on_invoice} onChange={e => setData('show_qr_on_invoice', e.target.checked)} />
                            <span className="text-gray-700 dark:text-gray-300">Show UPI QR on Invoice PDF</span>
                        </label>
                    </Section>

                    <button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-lg text-sm disabled:opacity-50">
                        {processing ? 'Saving…' : 'Save Settings'}
                    </button>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
