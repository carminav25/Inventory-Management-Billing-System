<?php
$pageTitle = "Add New Supplier";
$breadcrumbs = [
    ["name" => "Suppliers", "link" => "suppliers.php"],
    ["name" => "Add Supplier"]
];

require_once('layout.php');
?>

<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-slate-100">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Add New Supplier</h1>
                <p class="text-sm text-slate-500 mt-0.5">Fill in the details to register a new supplier.</p>
            </div>
        </div>

        <form action="../../process/admin/add_supplier.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Supplier Name <span class="text-red-500">*</span></label>
                <input type="text" name="supplier_name" placeholder="e.g., ABC Uniform Supplier" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Contact Person</label>
                    <input type="text" name="contact_person" placeholder="e.g., Juan Dela Cruz" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Contact Number</label>
                    <input type="text" name="contact_number" pattern="^(09\d{9}|\+639\d{9})$" title="Format: 09xxxxxxxxx or +639xxxxxxxxxx" placeholder="e.g., 09123456789" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Email Address</label>
                <input type="email" name="email" placeholder="e.g., supplier@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Address</label>
                <textarea name="address" rows="2" placeholder="Enter full address..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="suppliers.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<?php include_once('../../includes/footer.php'); ?>