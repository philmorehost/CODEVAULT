<?php
// High-Performance Product Editor Form
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
$thumbnail = $prodData ? htmlspecialchars($prodData['thumbnail']) : '';
$download_url = $prodData ? htmlspecialchars($prodData['download_url']) : '';
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

<div class="bg-white rounded-xl w-full max-w-3xl mx-auto p-8 border border-slate-200 shadow-lg relative">
    <div class="mb-8 border-b pb-4">
        <h3 id="product-modal-title" class="font-black text-2xl text-slate-900 tracking-tight">
            <?php echo $product_id ? 'Update System Asset' : 'Publish New Asset'; ?>
        </h3>
        <p class="text-sm text-slate-500 mt-1">Upload images directly from your device. Images are auto-optimized to WebP for lightning-fast submission.</p>
    </div>

    <form method="POST" action="index.php?action=product_save" class="space-y-6" id="product-editor-form" onsubmit="return handleSmartSubmit(event)">
        <input type="hidden" name="id" id="prod-input-id" value="<?php echo $product_id; ?>">
        
        <input type="hidden" name="thumbnail" id="hidden-thumbnail-url" value="<?php echo $thumbnail; ?>">
        <div id="hidden-screenshots-container"></div>

        <div class="bg-slate-50 p-6 rounded-lg border border-slate-100 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700 uppercase">Product Title *</label>
                    <input type="text" id="prod-input-title" name="title" required value="<?php echo $title; ?>" placeholder="e.g. PHP Fintech Script" class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-white text-sm focus:ring-2 focus:ring-[#5cb85c] shadow-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700 uppercase">Regular Price (₦) *</label>
                    <input type="number" step="0.01" name="price" required value="<?php echo $price; ?>" placeholder="50000" class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-white text-sm font-mono focus:ring-2 focus:ring-[#5cb85c] shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700 uppercase">Category *</label>
                    <select name="category" required class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-white text-sm focus:ring-2 focus:ring-[#5cb85c] shadow-sm">
                        <option value="">Select a Category...</option>
                        <?php
                        $cats = $db->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll();
                        foreach ($cats as $c) {
                            $sel = ($category === $c['name']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($c['name']) . '" ' . $sel . '>' . htmlspecialchars($c['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700 uppercase">Live Demo URL</label>
                    <input type="url" name="live_demo_url" value="<?php echo $demo_url; ?>" placeholder="https://demo.yoursite.com" class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-white text-sm focus:ring-2 focus:ring-[#5cb85c] shadow-sm">
                </div>
            </div>
        </div>

        <div class="bg-slate-50 p-6 rounded-lg border border-slate-100 space-y-6">
            
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-700 uppercase">Master Thumbnail *</label>
                <div class="flex items-center gap-4">
                    <div class="w-24 h-24 rounded border overflow-hidden bg-white shrink-0 relative shadow-sm border-gray-300">
                        <img id="thumb-preview-img" src="<?php echo $thumbnail ?: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&q=80&w=600'; ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <input type="file" id="local-thumb-input" accept="image/*" class="hidden" onchange="handleThumbnailSelect(this)">
                        <button type="button" onclick="document.getElementById('local-thumb-input').click()" class="px-4 py-2 bg-white border border-gray-300 hover:border-[#5cb85c] text-slate-700 text-xs font-bold rounded shadow-sm transition-colors outline-none">
                            Browse Local Image
                        </button>
                        <p class="text-[10px] text-slate-400 mt-2">Auto-converted to WebP (Max 1280px).</p>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200">

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-700 uppercase">Gallery Screenshots (Max 10)</label>
                    <span class="text-[10px] font-bold text-slate-400 font-mono" id="gallery-counter">0 / 10</span>
                </div>
                
                <input type="file" id="local-gallery-input" accept="image/*" multiple class="hidden" onchange="handleGallerySelect(this)">
                <div onclick="document.getElementById('local-gallery-input').click()" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-[#5cb85c] hover:bg-[#5cb85c]/5 transition-all bg-white">
                    <span class="text-2xl mb-2 block">📸</span>
                    <span class="text-xs font-bold text-slate-600">Click to Select Screenshots from your Device</span>
                    <p class="text-[9px] text-slate-400 mt-1">Images are kept local until you hit publish.</p>
                </div>

                <div id="gallery-preview-grid" class="grid grid-cols-3 md:grid-cols-5 gap-3 pt-4 select-none">
                    </div>
            </div>
            
            <hr class="border-gray-200">

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-700 uppercase">Source Code Download URL *</label>
                <input type="url" name="download_url" required value="<?php echo $download_url; ?>" placeholder="https://..." class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-white text-sm font-mono focus:ring-2 focus:ring-[#5cb85c] shadow-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border rounded-lg bg-white">
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-700 uppercase">System Tags</label>
                <input type="text" name="tags" value="<?php echo $tags; ?>" placeholder="vtu, fintech, portal" class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-slate-50 text-sm focus:ring-2 focus:ring-[#5cb85c] transition-all">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-700 uppercase">Version</label>
                <input type="text" name="version" value="<?php echo $version; ?>" placeholder="1.0.0" class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-slate-50 text-sm focus:ring-2 focus:ring-[#5cb85c] transition-all">
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-700 uppercase">Detailed Description & Requirements *</label>
            <textarea name="description" required rows="6" placeholder="Provide server requirements, installation guide, and features..." class="w-full px-4 py-3 rounded-md border-gray-300 outline-none bg-slate-50 text-sm leading-relaxed focus:ring-2 focus:ring-[#5cb85c] transition-all shadow-sm"><?php echo $description; ?></textarea>
        </div>

        <div class="pt-4 border-t border-slate-200">
            <div id="upload-progress-container" class="hidden mb-4 p-4 bg-emerald-50 border border-[#5cb85c] rounded-lg">
                <div class="flex justify-between text-xs font-bold text-slate-700 mb-2">
                    <span id="upload-status-text">Uploading to server...</span>
                </div>
                <div class="w-full bg-white h-2 rounded-full overflow-hidden border border-gray-200">
                    <div id="upload-progress-bar" class="bg-[#5cb85c] h-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <button type="submit" id="smart-publish-btn" class="w-full py-4 bg-[#5cb85c] hover:bg-[#4cae4c] text-white font-black text-sm uppercase tracking-widest rounded-lg shadow-md transition-all">
                Publish Asset to Marketplace
            </button>
        </div>
    </form>
</div>

<script>
    // State Management
    let localThumbBlob = null; 
    let localGalleryItems = []; // Array of objects: { id, blob, url }
    
    // Load existing gallery data if editing
    window.addEventListener('DOMContentLoaded', () => {
        let initialShots = [];
        try { initialShots = JSON.parse('<?php echo addslashes($preview_images); ?>') || []; } catch(e) {}
        
        initialShots.forEach(url => {
            if (url) {
                localGalleryItems.push({ id: Date.now() + Math.random(), blob: null, url: url });
            }
        });
        renderGalleryGrid();
    });

    // 1. Image to WebP Converter Engine (Keeps high quality, massively reduces size)
    function convertToWebP(file, quality = 0.85) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.naturalWidth;
                    let height = img.naturalHeight;
                    const MAX_DIMENSION = 1280; // Standardize massive images down

                    if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                        if (width > height) {
                            height = Math.round(height * (MAX_DIMENSION / width));
                            width = MAX_DIMENSION;
                        } else {
                            width = Math.round(width * (MAX_DIMENSION / height));
                            height = MAX_DIMENSION;
                        }
                    }
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob((webpBlob) => {
                        const baseName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                        resolve({ blob: webpBlob, name: baseName + '.webp' });
                    }, 'image/webp', quality);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // 2. Handle Thumbnail Selection
    function handleThumbnailSelect(input) {
        const file = input.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const btn = document.getElementById('smart-publish-btn');
        btn.innerHTML = "Optimizing Thumbnail...";
        btn.disabled = true;

        convertToWebP(file).then(({ blob, name }) => {
            localThumbBlob = { blob: blob, name: name };
            document.getElementById('thumb-preview-img').src = URL.createObjectURL(blob);
            
            btn.innerHTML = "Publish Asset to Marketplace";
            btn.disabled = false;
        });
        input.value = ''; // Reset input
    }

    // 3. Handle Gallery Selection
    function handleGallerySelect(input) {
        const files = input.files;
        if (!files.length) return;

        const remainingSlots = 10 - localGalleryItems.length;
        if (remainingSlots <= 0) {
            alert('Maximum limit of 10 screenshots reached.');
            return;
        }

        const filesToProcess = Array.from(files).slice(0, remainingSlots);
        const btn = document.getElementById('smart-publish-btn');
        btn.innerHTML = "Optimizing Screenshots...";
        btn.disabled = true;

        let processed = 0;
        filesToProcess.forEach(file => {
            if (!file.type.startsWith('image/')) {
                processed++; return;
            }
            
            convertToWebP(file).then(({ blob, name }) => {
                localGalleryItems.push({
                    id: Date.now() + Math.random(),
                    blob: blob,
                    name: name,
                    url: URL.createObjectURL(blob)
                });
                
                processed++;
                if (processed === filesToProcess.length) {
                    renderGalleryGrid();
                    btn.innerHTML = "Publish Asset to Marketplace";
                    btn.disabled = false;
                }
            });
        });
        input.value = ''; // Reset
    }

    function removeGalleryItem(id) {
        localGalleryItems = localGalleryItems.filter(item => item.id !== id);
        renderGalleryGrid();
    }

    function renderGalleryGrid() {
        const grid = document.getElementById('gallery-preview-grid');
        const counter = document.getElementById('gallery-counter');
        grid.innerHTML = '';
        counter.innerText = localGalleryItems.length + " / 10";

        localGalleryItems.forEach((item) => {
            const div = document.createElement('div');
            div.className = "relative aspect-video rounded border overflow-hidden bg-slate-100 group shadow-sm";
            div.innerHTML = `
                <img src="${item.url}" class="w-full h-full object-cover">
                <button type="button" onclick="removeGalleryItem(${item.id})" class="absolute top-1 right-1 w-5 h-5 rounded bg-red-500 text-white flex items-center justify-center text-xs opacity-80 hover:opacity-100 outline-none">✕</button>
            `;
            grid.appendChild(div);
        });
    }

    // 4. AJAX Upload Wrapper (Matches your existing backend logic)
    function uploadImageToBackend(blob, fileName, type) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', blob, fileName);
            formData.append('type', type);
            
            const titleInput = document.getElementById('prod-input-title');
            if(titleInput) formData.append('title', titleInput.value);

            fetch('index.php?action=image_upload_ajax', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') resolve(data.url);
                else reject(data.message || 'Upload failed');
            })
            .catch(err => reject('Server communication error'));
        });
    }

    // 5. The Magic Submission Handler (Uploads right before saving)
    async function handleSmartSubmit(e) {
        e.preventDefault();
        const form = e.target;
        
        // Ensure standard fields are filled
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        // Ensure thumbnail exists
        const hiddenThumb = document.getElementById('hidden-thumbnail-url').value;
        if (!localThumbBlob && !hiddenThumb) {
            alert('A Master Thumbnail image is required.');
            return false;
        }

        // Lock UI
        const btn = document.getElementById('smart-publish-btn');
        const progContainer = document.getElementById('upload-progress-container');
        const progBar = document.getElementById('upload-progress-bar');
        const statusText = document.getElementById('upload-status-text');
        
        btn.disabled = true;
        btn.innerHTML = 'Connecting to Server...';
        progContainer.classList.remove('hidden');

        try {
            let itemsToUpload = [];
            if (localThumbBlob) itemsToUpload.push({ item: localThumbBlob, type: 'thumbnail' });
            
            localGalleryItems.forEach(g => {
                if (g.blob) itemsToUpload.push({ item: g, type: 'screenshot' });
            });

            // Process Uploads Sequentially
            for (let i = 0; i < itemsToUpload.length; i++) {
                let pData = itemsToUpload[i];
                statusText.innerText = `Uploading Image ${i + 1} of ${itemsToUpload.length}...`;
                progBar.style.width = Math.round((i / itemsToUpload.length) * 100) + '%';
                
                const remoteUrl = await uploadImageToBackend(pData.item.blob, pData.item.name || 'image.webp', pData.type);
                
                if (pData.type === 'thumbnail') {
                    document.getElementById('hidden-thumbnail-url').value = remoteUrl;
                } else {
                    pData.item.url = remoteUrl; // Swap local objectURL with remote server URL
                }
            }

            // Finalize Form Inputs
            statusText.innerText = `Finalizing Asset Details...`;
            progBar.style.width = '100%';
            btn.innerHTML = 'Saving to Database...';

            // Inject final Gallery URLs into hidden inputs
            const hiddenContainer = document.getElementById('hidden-screenshots-container');
            hiddenContainer.innerHTML = '';
            localGalleryItems.forEach(g => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'screenshots[]';
                inp.value = g.url; // This is now the live server URL
                hiddenContainer.appendChild(inp);
            });

            // Submit native form to save!
            form.submit();

        } catch (error) {
            alert("Submission halted: " + error);
            btn.disabled = false;
            btn.innerHTML = 'Publish Asset to Marketplace';
            progContainer.classList.add('hidden');
        }
    }
</script>