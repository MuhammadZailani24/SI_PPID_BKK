<!-- faq.php -->
<?php 
require_once 'includes/koneksi.php'; 
include 'includes/header.php'; 

// =========================================================================
// AMBIL DATA FAQ DARI DATABASE SECARA DINAMIS
// =========================================================================
$data_faq = [];
$cek_tabel = $conn->query("SHOW TABLES LIKE 'faq'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    $query = $conn->query("SELECT * FROM faq ORDER BY id DESC");
    if ($query && $query->num_rows > 0) {
        while ($row = $query->fetch_assoc()) {
            $kat = $row['kategori'];
            $ikon = !empty($row['ikon']) ? $row['ikon'] : "📁";
            
            if (!isset($data_faq[$kat])) {
                $data_faq[$kat] = [
                    "kategori" => $kat,
                    "icon" => $ikon,
                    "pertanyaan" => []
                ];
            }
            $data_faq[$kat]['pertanyaan'][] = [
                "q" => $row['pertanyaan'],
                "a" => $row['jawaban']
            ];
        }
    }
}
?>

<style>
    /* =========================================================
       STYLING MODERN & ELEGAN FAQ (PERTANYAAN SERING DIAJUKAN)
       ========================================================= */
    .faq-wrapper {
        max-width: 950px;
        margin: 50px auto 90px auto;
        padding: 0 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* Kotak Pencarian FAQ Modern */
    .faq-search-box {
        position: relative;
        max-width: 600px;
        margin: 0 auto 45px auto;
    }

    .faq-search-input {
        width: 100%;
        padding: 16px 20px 16px 52px;
        border-radius: 35px;
        border: 1px solid #d1d5db;
        font-size: 1rem;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        outline: none;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        box-sizing: border-box;
        color: #1f2937;
    }

    .faq-search-input:focus {
        border-color: var(--primary, #00b2a9);
        box-shadow: 0 15px 35px rgba(0, 178, 169, 0.15);
        transform: translateY(-2px);
    }

    .search-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.1rem;
    }

    /* Kategori FAQ (Akordeon Utama) Modern */
    .faq-category {
        margin-bottom: 22px;
        background: #ffffff;
        border-radius: 18px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        border: 1px solid #eaeaea;
        transition: all 0.35s ease;
    }

    .faq-category:hover {
        border-color: #d1d5db;
        box-shadow: 0 12px 30px rgba(0, 178, 169, 0.08);
    }

    .faq-cat-header {
        background: linear-gradient(135deg, var(--primary, #00b2a9) 0%, #008f88 100%);
        color: white;
        padding: 22px 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.3s ease;
    }

    .faq-cat-header:hover {
        background: linear-gradient(135deg, #009e95, #007a73);
    }

    .faq-cat-title-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .faq-cat-icon {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        transition: all 0.3s ease;
    }

    .faq-category.active .faq-cat-icon {
        background: var(--hover-color, #ef8a1c);
        border-color: var(--hover-color, #ef8a1c);
    }

    .faq-cat-title {
        font-size: 1.15rem;
        font-weight: 800;
        letter-spacing: 0.3px;
    }

    .faq-cat-chevron {
        background: rgba(255, 255, 255, 0.15);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        font-size: 0.85rem;
    }

    .faq-category.active .faq-cat-chevron {
        transform: rotate(180deg);
        background: var(--hover-color, #ef8a1c);
    }

    /* Daftar Pertanyaan (Akordeon Sub) */
    .faq-cat-body {
        display: none;
        background: #fafafa;
        padding: 10px 25px;
        border-top: 1px solid #f0f0f0;
    }

    .faq-category.active .faq-cat-body {
        display: block;
    }

    .faq-item {
        border-bottom: 1px solid #eee;
        padding: 18px 0;
    }

    .faq-item:last-child {
        border-bottom: none;
    }

    .faq-q {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        font-size: 1rem;
        color: #1f2937;
        font-weight: 700;
        transition: color 0.3s ease;
    }

    .faq-q:hover {
        color: var(--hover-color, #ef8a1c);
    }

    .faq-q-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .faq-dot {
        width: 8px;
        height: 8px;
        background-color: var(--hover-color, #ef8a1c);
        border-radius: 50%;
        flex-shrink: 0;
    }

    .faq-q-chevron {
        background: #f1f5f9;
        color: #64748b;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        flex-shrink: 0;
    }

    .faq-item.active .faq-q-chevron {
        transform: rotate(180deg);
        background: var(--primary, #00b2a9);
        color: white;
    }

    .faq-a {
        display: none;
        padding: 15px 22px 10px 22px;
        color: #4b5563;
        font-size: 0.96rem;
        line-height: 1.75;
        text-align: justify;
        background: #ffffff;
        border-radius: 12px;
        margin-top: 12px;
        border: 1px solid #eaeaea;
    }

    .faq-item.active .faq-a {
        display: block;
    }

    /* Empty State jika belum ada data */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 18px;
        border: 1px dashed #d1d5db;
        color: #9ca3af;
        font-style: italic;
        font-size: 1.05rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
    }

    @media (max-width: 768px) {
        .faq-cat-header { padding: 18px 20px; }
        .faq-cat-title { font-size: 1.02rem; }
        .faq-cat-body { padding: 10px 15px; }
    }
</style>

<main>
    <section class="inner-header">
        <h1>Pertanyaan yang Sering Diajukan (FAQ)</h1>
        <p>Pusat bantuan dan informasi seputar layanan Keterbukaan Informasi Publik</p>
    </section>

    <div class="faq-wrapper">
        
        <?php if (!empty($data_faq)): ?>
            <!-- FITUR PENCARIAN -->
            <div class="faq-search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" class="faq-search-input" placeholder="Cari pertanyaan...">
            </div>

            <div id="faqContainer">
                <?php 
                $cat_index = 0;
                foreach($data_faq as $kategori) {
                    $cat_active_class = ($cat_index == 0) ? 'active' : '';
                ?>
                    <!-- KOTAK KATEGORI UTAMA -->
                    <div class="faq-category <?php echo $cat_active_class; ?>">
                        
                        <div class="faq-cat-header" onclick="toggleCategory(this)">
                            <div class="faq-cat-title-wrap">
                                <div class="faq-cat-icon"><?php echo htmlspecialchars($kategori['icon']); ?></div>
                                <div class="faq-cat-title"><?php echo htmlspecialchars($kategori['kategori']); ?></div>
                            </div>
                            <div class="faq-cat-chevron">▼</div>
                        </div>

                        <!-- KOTAK PERTANYAAN-PERTANYAAN -->
                        <div class="faq-cat-body">
                            <?php foreach($kategori['pertanyaan'] as $pertanyaan) { ?>
                                <div class="faq-item searchable-item">
                                    <div class="faq-q" onclick="toggleQuestion(this)">
                                        <div class="faq-q-left">
                                            <div class="faq-dot"></div>
                                            <span class="question-text"><?php echo htmlspecialchars($pertanyaan['q']); ?></span>
                                        </div>
                                        <div class="faq-q-chevron">▼</div>
                                    </div>
                                    <div class="faq-a answer-text">
                                        <?php echo nl2br(htmlspecialchars($pertanyaan['a'])); ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                <?php 
                    $cat_index++;
                } 
                ?>
            </div>
        <?php else: ?>
            <!-- Pemberitahuan jika admin belum mengunggah data FAQ -->
            <div class="empty-state">
                Belum ada pertanyaan atau data FAQ yang diunggah oleh admin.
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- JAVASCRIPT UNTUK EFEK KLIK (AKORDEON) DAN PENCARIAN -->
<script>
    function toggleCategory(element) {
        const parentCategory = element.parentElement;
        parentCategory.classList.toggle('active');
    }

    function toggleQuestion(element) {
        const parentItem = element.parentElement;
        const siblings = parentItem.parentElement.querySelectorAll('.faq-item');
        siblings.forEach(sibling => {
            if (sibling !== parentItem) {
                sibling.classList.remove('active');
            }
        });
        parentItem.classList.toggle('active');
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let categories = document.querySelectorAll('.faq-category');

            categories.forEach(category => {
                let items = category.querySelectorAll('.searchable-item');
                let hasVisibleItem = false;

                items.forEach(item => {
                    let questionText = item.querySelector('.question-text').innerText.toLowerCase();
                    let answerText = item.querySelector('.answer-text').innerText.toLowerCase();
                    
                    if (questionText.includes(filter) || answerText.includes(filter)) {
                        item.style.display = 'block';
                        hasVisibleItem = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (hasVisibleItem) {
                    category.style.display = 'block';
                    if (filter !== "") {
                        category.classList.add('active');
                    }
                } else {
                    category.style.display = 'none';
                }
            });
        });
    }
</script>

<?php include 'includes/footer.php'; ?>