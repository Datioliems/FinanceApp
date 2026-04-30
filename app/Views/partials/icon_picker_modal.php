<?php
// Danh sách các icon phổ biến hay dùng cho quản lý chi tiêu
$commonIcons = [
    'bi-tag', 'bi-tags', 'bi-bag', 'bi-cart', 'bi-cart3', 'bi-basket', 'bi-basket2',
    'bi-cup-hot', 'bi-cup-straw', 'bi-egg-fried', 'bi-apple',
    'bi-house', 'bi-shop', 'bi-building', 'bi-bank',
    'bi-car-front', 'bi-fuel-pump', 'bi-bus-front', 'bi-train-freight-front', 'bi-airplane', 'bi-bicycle',
    'bi-heart-pulse', 'bi-hospital', 'bi-bandaid', 'bi-prescription2',
    'bi-book', 'bi-mortarboard', 'bi-journal-text',
    'bi-controller', 'bi-dice-5', 'bi-film', 'bi-music-note-beamed', 'bi-ticket-perforated',
    'bi-piggy-bank', 'bi-wallet2', 'bi-cash-stack', 'bi-cash-coin', 'bi-credit-card', 'bi-receipt', 'bi-receipt-cutoff', 'bi-graph-up-arrow',
    'bi-gift', 'bi-balloon', 'bi-cake', 'bi-stars',
    'bi-laptop', 'bi-phone', 'bi-tv', 'bi-headphones', 'bi-camera', 'bi-watch',
    'bi-tools', 'bi-wrench', 'bi-hammer', 'bi-scissors', 'bi-palette', 'bi-brush',
    'bi-droplet', 'bi-lightning', 'bi-fire', 'bi-lightbulb',
    'bi-tree', 'bi-flower1', 'bi-moon', 'bi-cloud-sun', 'bi-umbrella',
    'bi-person', 'bi-people', 'bi-briefcase', 'bi-emoji-smile',
    'bi-box-seam', 'bi-bookmark', 'bi-clipboard-data'
];
?>
<div class="modal fade" id="iconPickerModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Chọn Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <?php foreach ($commonIcons as $icon): ?>
                    <div class="col-2 text-center">
                        <button type="button" class="btn btn-light border w-100 p-2 icon-picker-btn" data-icon="<?= $icon ?>" style="border-radius: 10px;">
                            <i class="<?= $icon ?> fs-5"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let targetInputId = null;

    const iconPickerModalEl = document.getElementById('iconPickerModal');
    if (iconPickerModalEl) {
        iconPickerModalEl.addEventListener('show.bs.modal', function(e) {
            const triggerEl = e.relatedTarget;
            if (triggerEl) {
                targetInputId = triggerEl.getAttribute('data-target-input');
            }
        });

        const iconBtns = iconPickerModalEl.querySelectorAll('.icon-picker-btn');
        iconBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const selectedIcon = this.getAttribute('data-icon');
                if (targetInputId) {
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = selectedIcon;
                        // Kích hoạt sự kiện input để các script live preview (nếu có) chạy cập nhật giao diện
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
                const modalInstance = bootstrap.Modal.getInstance(iconPickerModalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        });
    }
});
</script>
