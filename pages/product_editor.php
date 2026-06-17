<?php
// Product Editor Form (extracted from modal)
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$prodData = null;
if ($product_id > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND (seller_id = ? OR ? = 1)");
    $stmt->execute([$product_id, $_SESSION['user_id'], is_admin() ? 1 : 0]);
    $prodData = $stmt->fetch();
}

$title = $prodData ? htmlspecialchars($prodData['title']) : '';
$price = $prodData ? $prodData['price'] : '';
$category = $prodData ? htmlspecialchars($prodData['category']) : '';
$demo_url = $prodData ? htmlspecialchars($prodData['live_demo_url']) : '';
$description = $prodData ? htmlspecialchars($prodData['description']) : '';
$thumbnail = $prodData ? htmlspecialchars($prodData['thumbnail']) : 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&q=80&w=600';
$download_url = $prodData ? htmlspecialchars($prodData['download_url']) : 'https://example.com/source_code.zip';
$tags = $prodData ? htmlspecialchars($prodData['tags']) : '';
$version = $prodData ? htmlspecialchars($prodData['version']) : '1.0.0';
$discount_price = $prodData ? $prodData['discount_price'] : '';
$sale_ends_at = $prodData && $prodData['sale_ends_at'] ? substr($prodData['sale_ends_at'], 0, 16) : '';
$status = $prodData ? $prodData['status'] : 'pending';
$is_featured = $prodData ? $prodData['is_featured'] : 0;
$licensing_enabled = $prodData ? $prodData['licensing_enabled'] : 0;
$extended_price = $prodData ? $prodData['extended_price'] : '';
$preview_images = $prodData ? $prodData['preview_images'] : '[]';
?>

<div class="bg-white rounded-lg w-full max-w-2xl mx-auto p-8 border shadow-sm relative">
    <h3 id="product-modal-title" class="font-black text-xl text-slate-900 mb-2"><?php echo $product_id ? 'Edit Code script' : 'Publish Code script'; ?></h3>
    <p class="text-xs text-slate-500 mb-6">Listed products are peer-vetted automatically. Earn split royalties instantly through verified Paystack settlement triggers.</p>

    <form method="POST" action="index.php?action=product_save" class="space-y-4" id="product-editor-form">
        <input type="hidden" name="id" id="prod-input-id" value="<?php echo $product_id; ?>">

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Product Title</label>
                <input type="text" name="title" id="prod-input-title" required value="<?php echo $title; ?>" placeholder="e.g. SaaS Boilerplate..." class="w-full px-4 py-3 rounded border outline-none bg-white text-xs font-bold focus:border-[#5cb85c] shadow-sm">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Regular Price</label>
                <input type="number" step="0.01" name="price" id="prod-input-price" required value="<?php echo $price; ?>" placeholder="49.99" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs font-mono focus:border-[#5cb85c] shadow-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Category</label>
                <select name="category" id="prod-input-category" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs font-bold focus:border-[#5cb85c] shadow-sm">
                    <?php
                    $cats = $db->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll();
                    foreach ($cats as $c) {
                        $sel = ($category === $c['name']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($c['name']) . '" ' . $sel . '>' . htmlspecialchars($c['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Live Demo URL (Optional)</label>
                <input type="url" name="live_demo_url" id="prod-input-demo" value="<?php echo $demo_url; ?>" placeholder="https://demo.example.com" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs focus:border-[#5cb85c] shadow-sm">
            </div>
        </div>

        <!-- Thumbnail Manager -->
        <div class="space-y-2 p-4 border rounded bg-slate-50/50">
            <div class="flex items-center justify-between">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Master Thumbnail</label>
                <div class="flex rounded text-[10px] overflow-hidden">
                    <button type="button" onclick="setThumbMode('url')" id="btn-thumb-mode-url" class="px-2.5 py-1 font-bold text-white bg-[#5cb85c] rounded-l border border-[#5cb85c] outline-none">URL</button>
                    <button type="button" onclick="setThumbMode('file')" id="btn-thumb-mode-file" class="px-2.5 py-1 font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-r border border-gray-300 outline-none">Upload</button>
                </div>
            </div>
            <div class="flex gap-4 items-start">
                <div class="w-24 h-24 rounded border shrink-0 overflow-hidden bg-slate-100">
                    <img id="prod-thumb-preview-img" src="<?php echo $thumbnail; ?>" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 space-y-2">
                    <div id="thumb-input-pane-url" class="">
                        <input type="url" name="thumbnail" id="prod-input-thumbnail" required value="<?php echo $thumbnail; ?>" placeholder="https://..." onchange="updateThumbPreview(this.value)" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs focus:border-[#5cb85c] shadow-sm">
                    </div>
                    <div id="thumb-input-pane-file" class="hidden">
                        <div id="thumb-dropzone" class="border-2 border-dashed border-slate-350 rounded p-4 text-center cursor-pointer hover:border-[#5cb85c] transition-colors relative flex flex-col items-center justify-center bg-white h-[42px]">
                            <span class="text-xs text-slate-600 font-bold" id="thumb-upload-text">📁 Click or Drop Thumbnail File</span>
                            <input type="file" id="thumb-file-input" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <div id="thumb-upload-progress" class="w-full bg-slate-200 h-1.5 rounded-full mt-2 hidden overflow-hidden">
                            <div id="thumb-upload-progress-bar" class="bg-[#5cb85c] h-full" style="width: 0%"></div>
                        </div>
                    </div>
                    <p class="text-[9px] text-slate-400">High-resolution cover image. 16:9 ratio recommended.</p>
                </div>
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Secured Source Code Download URL</label>
            <input type="url" name="download_url" id="prod-input-zip" required value="<?php echo $download_url; ?>" placeholder="https://example.com/source.zip" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs font-mono focus:border-[#5cb85c] shadow-sm">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">System Tags (CSV)</label>
                <input type="text" name="tags" id="prod-input-tags" value="<?php echo $tags; ?>" placeholder="php, template, tailwind" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs focus:border-[#5cb85c] shadow-sm">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Release Version</label>
                <input type="text" name="version" id="prod-input-version" value="<?php echo $version; ?>" placeholder="1.0.0" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs focus:border-[#5cb85c] shadow-sm">
            </div>
        </div>

        <!-- Product Licensing Settings -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Require Script License key</h4>
                    <p class="text-[9px] text-slate-455 leading-normal">Generate license keys automatically on purchase using an external License Manager API.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        name="licensing_enabled"
                        id="prod-input-licensing-enabled"
                        value="1"
                        <?php echo $licensing_enabled ? 'checked' : ''; ?>
                        class="sr-only peer"
                        onchange="toggleLicensingFields()"
                    >
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer:checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5cb85c]"></div>
                </label>
            </div>
            <div id="licensing-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4 <?php echo $licensing_enabled ? '' : 'hidden'; ?>">
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Extended License Price (Optional)</label>
                    <input type="number" step="0.01" name="extended_price" id="prod-input-extended-price" value="<?php echo $extended_price; ?>" placeholder="Leave empty if not offering extended license" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs font-mono focus:border-[#5cb85c] shadow-sm">
                </div>
                <div class="sm:col-span-2 text-[10px] text-slate-500 bg-white border p-3 rounded leading-relaxed">
                    💡 <strong>Integration Note:</strong> To validate this license within your script, use the central validation snippet provided by the platform. Check the documentation for the <code>license-verifier.php</code> integration.
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 border-t pt-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-orange-500 uppercase tracking-widest">Discount/Sale Price (Optional)</label>
                <input type="number" step="0.01" name="discount_price" id="prod-input-discount" value="<?php echo $discount_price; ?>" placeholder="Leave empty if no sale" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs focus:border-orange-400 shadow-sm">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-orange-500 uppercase tracking-widest">Sale Ends At (Optional)</label>
                <input type="datetime-local" name="sale_ends_at" id="prod-input-sale-ends" value="<?php echo $sale_ends_at; ?>" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs focus:border-orange-400 shadow-sm">
            </div>
        </div>

        <?php if (is_admin()): ?>
            <div class="space-y-1 border-t pt-4">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Approval Status</label>
                <select name="status" id="prod-input-status" class="w-full px-4 py-3 rounded border outline-none bg-white text-xs font-bold focus:border-[#5cb85c] shadow-sm">
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved (Live)</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
        <?php endif; ?>

        <!-- Screenshots input lists -->
        <div class="space-y-3 p-4 border rounded bg-slate-50/50">
            <div class="flex items-center justify-between">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Gallery Screenshots (Max 10)</label>
                <span class="text-[10px] font-bold text-slate-400 font-mono" id="screenshot-counter">0 / 10</span>
            </div>

            <!-- Drag & Drop Zone -->
            <div id="screenshots-dropzone" class="border-2 border-dashed border-slate-350 rounded-lg p-5 text-center cursor-pointer hover:border-[#5cb85c] transition-colors relative flex flex-col items-center justify-center bg-white">
                <span class="text-xl mb-1">🖼️</span>
                <span class="text-xs text-slate-600 font-bold" id="shots-upload-text">Drag & Drop Screenshots here, or click to browse</span>
                <span class="text-[9px] text-slate-400 mt-0.5">Supports PNG, JPG, WEBP, GIF (Automatically optimized)</span>
                <input type="file" id="screenshots-file-input" accept="image/*" multiple class="absolute inset-0 opacity-0 cursor-pointer">
            </div>

            <!-- Manual input block -->
            <div class="flex gap-2">
                <input type="url" id="screenshot-manual-url" placeholder="https://example.com/screenshot.jpg" class="flex-1 px-3 py-2 rounded border bg-white outline-none text-xs focus:border-[#5cb85c]">
                <button type="button" onclick="addManualScreenshotUrl()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded text-xs transition-colors shrink-0 outline-none">Add URL</button>
            </div>

            <!-- Previews grid -->
            <div id="screenshots-preview-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 select-none">
                <!-- Previews injected here -->
            </div>

            <!-- Hidden inputs container -->
            <div id="screenshots-hidden-inputs"></div>
        </div>

        <div class="space-y-1">
            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Detailed README Documentation</label>
            <textarea name="description" id="prod-input-desc" required rows="4" placeholder="Detail installation requirements, setup guides, and library dependencies..." class="w-full px-4 py-3 rounded border outline-none bg-white text-xs leading-relaxed focus:border-[#5cb85c] shadow-sm"><?php echo $description; ?></textarea>
        </div>

        <!-- Featured checkbox toggle -->
        <div class="p-4 bg-slate-50 border border-slate-100 rounded flex items-center justify-between">
            <div>
                <h4 class="text-xs font-bold text-slate-800">Feature this product</h4>
                <p class="text-[9px] text-slate-450 leading-normal max-w-xs">Checking this will tag the script as Featured and trigger price/announcement notifications for wishlisters.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    name="is_featured"
                    id="prod-input-featured"
                    value="1"
                    <?php echo $is_featured ? 'checked' : ''; ?>
                    class="sr-only peer"
                >
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer:checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5cb85c]"></div>
            </label>
        </div>

        <button type="button" id="publish-asset-btn" class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-extrabold uppercase rounded text-xs shadow transition-all">
            Publish System Asset
        </button>
    </form>
</div>

<script>
    // Initialize initial screenshots if editing
    window.addEventListener('DOMContentLoaded', () => {
        let initialShots = [];
        try {
            initialShots = JSON.parse('<?php echo addslashes($preview_images); ?>') || [];
        } catch(e) {}
        initialShots.forEach(s => {
            if (s) {
                uploadedScreenshots.push({
                    url: s,
                    originalUrl: s,
                    isUploading: false,
                    progress: 100,
                    name: 'screenshot-saved'
                });
            }
        });
        if(typeof renderScreenshotsGrid === 'function') {
            renderScreenshotsGrid();
        }
    });
</script>
