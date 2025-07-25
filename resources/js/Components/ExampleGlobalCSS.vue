<template>
    <div class="pos-card pos-fade-in">
        <div class="pos-card-header">
            <h3 class="pos-heading-3">Contoh Penggunaan Global CSS</h3>
        </div>
        
        <div class="pos-card-body">
            <!-- Menggunakan Global CSS Classes -->
            <div class="pos-row">
                <div class="pos-col-6">
                    <div class="pos-form-group">
                        <label class="pos-form-label">Nama Produk:</label>
                        <input 
                            type="text" 
                            class="pos-form-input"
                            v-model="productName"
                            placeholder="Masukkan nama produk"
                        />
                    </div>
                </div>
                
                <div class="pos-col-6">
                    <div class="pos-form-group">
                        <label class="pos-form-label">Harga:</label>
                        <input 
                            type="number" 
                            class="pos-form-input"
                            v-model="price"
                            placeholder="Masukkan harga"
                        />
                    </div>
                </div>
            </div>

            <!-- Global Button Classes -->
            <div class="pos-flex-between pos-mt-4">
                <button class="pos-btn pos-btn-outline" @click="resetForm">
                    Reset
                </button>
                <div>
                    <button class="pos-btn pos-btn-danger pos-mt-2" @click="deleteItem">
                        Hapus
                    </button>
                    <button class="pos-btn pos-btn-success pos-mt-2" @click="saveItem">
                        Simpan
                    </button>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="pos-mt-4">
                <span class="status-badge success">Aktif</span>
                <span class="status-badge warning">Pending</span>
                <span class="status-badge danger">Nonaktif</span>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="loading-container">
                <div class="loading-spinner"></div>
                <p>Memuat data...</p>
            </div>

            <!-- Table dengan Global CSS -->
            <div class="pos-table-container pos-mt-4">
                <table class="pos-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Produk A</td>
                            <td>Rp 100,000</td>
                            <td><span class="status-badge success">Aktif</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Produk B</td>
                            <td>Rp 150,000</td>
                            <td><span class="status-badge warning">Pending</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pos-card-footer">
            <small class="pos-text-muted">
                * Contoh penggunaan global CSS untuk konsistensi design
            </small>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

// Reactive data
const productName = ref('');
const price = ref('');
const loading = ref(false);

// Methods
const resetForm = () => {
    productName.value = '';
    price.value = '';
};

const saveItem = () => {
    loading.value = true;
    // Simulate API call
    setTimeout(() => {
        loading.value = false;
        alert('Data berhasil disimpan!');
    }, 2000);
};

const deleteItem = () => {
    if (confirm('Yakin ingin menghapus?')) {
        alert('Data berhasil dihapus!');
    }
};

// Contoh menggunakan global CSS via plugin
const { $css, $utils } = getCurrentInstance().appContext.config.globalProperties;

// Example: Dynamic class generation
const dynamicClass = $utils.generateClass('pos-btn', {
    primary: true,
    large: false,
    disabled: false
});

console.log('Available CSS colors:', $css.colors);
console.log('Dynamic class:', dynamicClass);
</script>

<style scoped>
/* Component-specific styles bisa ditambahkan di sini */
/* Ini akan override global styles jika diperlukan */

.custom-component-style {
    /* Menggunakan CSS variables dari global */
    color: var(--primary-color);
    padding: var(--spacing-md);
    border-radius: var(--border-radius);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pos-flex-between {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>
