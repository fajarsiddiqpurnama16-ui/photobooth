<?php
/* =========================================================
   DATA DETAIL ORDER UNTUK POPUP
========================================================= */

$payLabel = [
    'paid'     => 'Lunas',
    'rejected' => 'Ditolak',
    'pending'  => 'Belum bayar',
    'unpaid'   => 'Belum bayar'
];

$ordLabel = [
    'completed' => 'Selesai',
    'processing'=> 'Diproses',
    'pending'   => 'Pending',
    'cancelled' => 'Dibatalkan'
];

$detailJs = [];

foreach ($orders as $o) {

    $detailJs[$o->id_order] = [

        ['Kode order',
            $o->order_code
        ],

        ['Waktu order',
            date('d M Y, H:i:s', strtotime($o->created_at))
        ],

        ['Customer',
            $o->customer_name ?: '-'
        ],

        ['No. HP',
            $o->customer_phone ?: '-'
        ],

        ['Template',
            $o->template_name ?: 'Custom Strip'
        ],

        ['Paket',
            $o->package_name ?: '-'
        ],

        ['Qty cetak',
            $o->copies . ' lembar'
        ],

        ['Total bayar',
            'Rp ' . number_format(
                $o->total_amount,
                0,
                ',',
                '.'
            )
        ],

        ['Metode bayar',
            $o->payment_method ?: '-'
        ],

        ['Status bayar',
            $payLabel[$o->payment_status]
                ?? $o->payment_status
        ],

        ['Status order',
            $ordLabel[$o->order_status]
                ?? $o->order_status
        ],
    ];
}
?>

<style>

/* =========================================================
   ORDER PAGINATION
========================================================= */

.order-pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 4px 4px;
    flex-wrap: wrap;
}

.order-pagination-info {
    color: var(--text-muted);
    font-size: 13px;
    white-space: nowrap;
}

.order-pagination {
    display: flex;
    align-items: center;
    gap: 6px;
}

.order-page-numbers {
    display: flex;
    align-items: center;
    gap: 6px;
}

.order-page-btn,
.order-page-number {
    height: 34px;
    min-width: 34px;
    padding: 0 10px;
    border: 1px solid var(--line);
    background: #fff;
    color: var(--text);
    border-radius: 7px;
    font-size: 13px;
    cursor: pointer;
    transition: .15s ease;
}

.order-page-btn:hover:not(:disabled),
.order-page-number:hover {
    background: #f8fafc;
}

.order-page-number.active {
    background: #0f2f6b;
    border-color: #0f2f6b;
    color: #fff;
    font-weight: 600;
}

.order-page-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.order-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
    font-size: 13px;
}

.order-per-page select {
    height: 34px;
    padding: 0 28px 0 10px;
    border: 1px solid var(--line);
    border-radius: 7px;
    background: #fff;
    color: var(--text);
    font-size: 13px;
    cursor: pointer;
}

/* =========================================================
   MODAL
========================================================= */

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.45);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 1000;
}

.modal-overlay.open {
    display: flex;
}

.modal {
    background: #fff;
    color: #0f172a;
    border-radius: 12px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 24px 60px rgba(15,23,42,.25);
    overflow: hidden;
}

.modal-h {
    padding: 18px 22px 0;
}

.modal-h h2 {
    font-size: 17px;
    font-weight: 700;
    letter-spacing: -.01em;
    color: #0f172a;
}

.modal-h p {
    color: #64748b;
    margin-top: 4px;
}

.modal-b {
    padding: 16px 22px;
}

.modal-f {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 22px 20px;
}

.dl {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.dl div {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding: 9px 12px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 13px;
}

.dl div:last-child {
    border-bottom: 0;
}

.dl span {
    color: #64748b;
}

.dl b {
    font-weight: 600;
    text-align: right;
    word-break: break-word;
    color: #0f172a;
}

.btn-icon {
    padding: 4px 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-icon .ic {
    width: 15px;
    height: 15px;
}

.btn-yes-danger {
    background: #dc2626;
    border-color: #dc2626;
    color: #fff;
}

.btn-yes-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
    color: #fff;
}

@media (max-width: 700px) {

    .order-pagination-wrap {
        align-items: flex-start;
        flex-direction: column;
    }

    .order-pagination {
        width: 100%;
        justify-content: center;
    }

    .order-per-page {
        width: 100%;
        justify-content: flex-end;
    }

}

</style>


<!-- =========================================================
     HEADER
========================================================= -->

<div class="topbar">

    <div class="page-header">

        <h1> Daftar Orderan Masuk</h1>

        <p>
            Kelola seluruh transaksi sesi photobooth,
            status pembayaran, dan pengiriman cetak.
        </p>

    </div>

    <div class="topbar-actions">

        <a
            href="<?= site_url('Admin/add_mock_order') ?>"
            class="btn btn-secondary btn-sm"
            title="Simulasi pesanan masuk untuk tes data"
        >
            <span>⚡ Simulasi Order Baru</span>
        </a>

    </div>

</div>


<!-- =========================================================
     SUMMARY
========================================================= -->

<div
    style="
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:16px;
        margin-bottom:24px;
    "
>

    <div class="card" style="padding:16px;">

        <span
            style="
                font-size:11px;
                text-transform:uppercase;
                color:var(--text-muted);
                font-weight:700;
            "
        >
            Total Order
        </span>

        <div
            style="
                font-size:22px;
                font-weight:800;
                color:#ffffff;
                margin-top:2px;
            "
        >
            <?= $summary['total_orders'] ?>
        </div>

    </div>


    <div class="card" style="padding:16px;">

        <span
            style="
                font-size:11px;
                text-transform:uppercase;
                color:var(--text-muted);
                font-weight:700;
            "
        >
            Total Penghasilan
        </span>

        <div
            style="
                font-size:22px;
                font-weight:800;
                color:var(--emerald);
                margin-top:2px;
            "
        >
            Rp <?= number_format(
                $summary['total_revenue'],
                0,
                ',',
                '.'
            ) ?>
        </div>

    </div>


    <div class="card" style="padding:16px;">

        <span
            style="
                font-size:11px;
                text-transform:uppercase;
                color:var(--text-muted);
                font-weight:700;
            "
        >
            Order Hari Ini
        </span>

        <div
            style="
                font-size:22px;
                font-weight:800;
                color:var(--cyan-light);
                margin-top:2px;
            "
        >
            <?= $summary['today_orders'] ?> pesanan
        </div>

    </div>


    <div class="card" style="padding:16px;">

        <span
            style="
                font-size:11px;
                text-transform:uppercase;
                color:var(--text-muted);
                font-weight:700;
            "
        >
            Menunggu / Diproses
        </span>

        <div
            style="
                font-size:22px;
                font-weight:800;
                color:var(--amber);
                margin-top:2px;
            "
        >
            <?= (
                $summary['status_pending']
                +
                $summary['status_processing']
            ) ?>
            order
        </div>

    </div>

</div>


<!-- =========================================================
     TABLE CARD
========================================================= -->

<div class="card">

    <!-- SEARCH -->
    <div
        class="card-header"
        style="
            flex-wrap:wrap;
            gap:12px;
        "
    >

        <div
            style="
                display:flex;
                align-items:center;
                gap:12px;
            "
        >

            <input
                type="text"
                id="orderSearchInput"
                placeholder="Cari kode order, nama pelanggan..."
                style="
                    width:260px;
                    padding:7px 12px;
                    font-size:13px;
                "
            >

            <select
                id="statusFilter"
                style="
                    width:160px;
                    padding:7px 12px;
                    font-size:13px;
                "
            >

                <option value="all">
                    Semua Status
                </option>

                <option value="completed">
                    Selesai
                </option>

                <option value="processing">
                    Diproses
                </option>

                <option value="pending">
                    Pending
                </option>

                <option value="cancelled">
                    Dibatalkan
                </option>

            </select>

        </div>


        <div
            style="
                font-size:12px;
                color:var(--text-muted);
            "
            id="ordersCounter"
        >
            Menampilkan <?= count($orders) ?> orderan
        </div>

    </div>


    <!-- TABLE -->

    <div class="table-responsive">

        <table id="ordersTable">

            <thead>

                <tr>

                    <th>Kode Order & Waktu</th>

                    <th>Customer</th>

                    <th>Template & Paket</th>

                    <th>Qty Cetak</th>

                    <th>Total Bayar</th>

                    <th>Pembayaran</th>

                    <th>Status Order</th>

                    <th>Aksi Cepat</th>

                </tr>

            </thead>


            <tbody id="ordersTableBody">

                <?php if (!empty($orders)): ?>

                    <?php foreach ($orders as $o): ?>

                        <?php
                        $isCancelled =
                            ($o->order_status === 'cancelled');
                        ?>

                        <tr
                            class="order-row"
                            data-status="<?= htmlspecialchars($o->order_status) ?>"
                        >

                            <!-- KODE -->
                            <td>

                                <div
                                    style="
                                        font-family:'JetBrains Mono',monospace;
                                        font-weight:600;
                                        color:var(--cyan-light);
                                        font-size:12.5px;
                                    "
                                >
                                    <?= htmlspecialchars($o->order_code) ?>
                                </div>

                                <div
                                    style="
                                        font-size:11px;
                                        color:var(--text-muted);
                                        margin-top:2px;
                                    "
                                >
                                    <?= date(
                                        'd M Y, H:i',
                                        strtotime($o->created_at)
                                    ) ?>
                                </div>

                            </td>


                            <!-- CUSTOMER -->
                            <td>

                                <div
                                    class="customer-name"
                                    style="
                                        font-weight:600;
                                        color:#ffffff;
                                        font-size:13.5px;
                                    "
                                >
                                    <?= htmlspecialchars($o->customer_name) ?>
                                </div>

                                <div
                                    style="
                                        font-size:11px;
                                        color:var(--text-muted);
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $o->customer_phone ?: '-'
                                    ) ?>
                                </div>

                            </td>


                            <!-- TEMPLATE -->
                            <td>

                                <div
                                    style="
                                        font-weight:500;
                                        color:#ffffff;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $o->template_name
                                        ?: 'Custom Strip'
                                    ) ?>
                                </div>

                                <div
                                    style="
                                        font-size:11px;
                                        color:var(--text-muted);
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $o->package_name
                                    ) ?>
                                </div>

                            </td>


                            <!-- QTY -->
                            <td>

                                <span
                                    class="badge badge-purple"
                                    style="font-size:10px;"
                                >
                                    <?= $o->copies ?> Lembar
                                </span>

                            </td>


                            <!-- TOTAL -->
                            <td>

                                <?php if ($isCancelled): ?>

                                    <div
                                        style="
                                            font-weight:600;
                                            color:var(--text-muted);
                                            font-size:14px;
                                            text-decoration:line-through;
                                        "
                                    >
                                        Rp <?= number_format(
                                            $o->total_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </div>

                                <?php else: ?>

                                    <div
                                        style="
                                            font-weight:700;
                                            color:var(--emerald);
                                            font-size:14px;
                                        "
                                    >
                                        Rp <?= number_format(
                                            $o->total_amount,
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- PEMBAYARAN -->
                            <td>

                                <div
                                    style="
                                        font-weight:600;
                                        color:#ffffff;
                                        font-size:12px;
                                    "
                                >
                                    <?= htmlspecialchars(
                                        $o->payment_method
                                    ) ?>
                                </div>


                                <?php if ($o->payment_status === 'paid'): ?>

                                    <span
                                        style="
                                            font-size:10px;
                                            color:#34d399;
                                        "
                                    >
                                        ● Lunas
                                    </span>

                                <?php elseif ($o->payment_status === 'rejected'): ?>

                                    <span
                                        style="
                                            font-size:10px;
                                            color:var(--red);
                                        "
                                    >
                                        ● Ditolak
                                    </span>

                                <?php else: ?>

                                    <span
                                        style="
                                            font-size:10px;
                                            color:#fbbf24;
                                        "
                                    >
                                        ● Belum Bayar
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->
                            <td>

                                <?php if ($o->order_status === 'completed'): ?>

                                    <span class="badge badge-green">
                                        <span class="badge-dot"></span>
                                        Selesai
                                    </span>

                                <?php elseif ($o->order_status === 'processing'): ?>

                                    <span class="badge badge-cyan">
                                        <span class="badge-dot"></span>
                                        Diproses
                                    </span>

                                <?php elseif ($isCancelled): ?>

                                    <span class="badge badge-red">
                                        <span class="badge-dot"></span>
                                        Dibatalkan
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-amber">
                                        <span class="badge-dot"></span>
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- AKSI -->
                            <td>

                                <div
                                    style="
                                        display:flex;
                                        align-items:center;
                                        gap:6px;
                                    "
                                >

                                    <!-- DETAIL -->
                                    <button
                                        type="button"
                                        class="
                                            btn
                                            btn-secondary
                                            btn-sm
                                            btn-icon
                                            js-detail
                                        "
                                        data-id="<?= (int)$o->id_order ?>"
                                        title="Lihat detail"
                                        aria-label="Lihat detail"
                                    >

                                        <svg
                                            class="ic"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>

                                    </button>


                                    <!-- DELETE -->
                                    <button
                                        type="button"
                                        class="
                                            btn
                                            btn-danger
                                            btn-sm
                                            btn-icon
                                            js-delete
                                        "
                                        data-id="<?= (int)$o->id_order ?>"
                                        data-code="<?= htmlspecialchars($o->order_code) ?>"
                                        data-url="<?= site_url(
                                            'Admin/delete_order/'
                                            . $o->id_order
                                        ) ?>"
                                        title="Hapus"
                                        aria-label="Hapus"
                                    >

                                        <svg
                                            class="ic"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="M19 6l-1 14H6L5 6"/>
                                            <path d="M10 11v6"/>
                                            <path d="M14 11v6"/>
                                        </svg>

                                    </button>


                                    <!-- SELESAI -->
                                    <?php if (
                                        !$isCancelled
                                        &&
                                        $o->order_status !== 'completed'
                                    ): ?>

                                        <a
                                            href="<?= site_url(
                                                'Admin/update_order_status/'
                                                . $o->id_order
                                            ) ?>?status=completed&redirect=Admin/orders"
                                            class="btn btn-success btn-sm"
                                            style="
                                                padding:4px 8px;
                                                font-size:11px;
                                            "
                                        >
                                            ✓ Selesai
                                        </a>

                                    <?php endif; ?>


                                    <!-- PROSES -->
                                    <?php if (
                                        $o->order_status === 'pending'
                                    ): ?>

                                        <a
                                            href="<?= site_url(
                                                'Admin/update_order_status/'
                                                . $o->id_order
                                            ) ?>?status=processing&redirect=Admin/orders"
                                            class="btn btn-warning btn-sm"
                                            style="
                                                padding:4px 8px;
                                                font-size:11px;
                                            "
                                        >
                                            Proses
                                        </a>

                                    <?php endif; ?>


                                    <!-- VERIFIED -->
                                    <?php if (
                                        $o->order_status === 'completed'
                                    ): ?>

                                        <span
                                            style="
                                                color:var(--emerald);
                                                font-size:11.5px;
                                                font-weight:600;
                                            "
                                        >
                                            Terverifikasi
                                        </span>

                                    <?php endif; ?>


                                    <!-- CANCELLED -->
                                    <?php if ($isCancelled): ?>

                                        <span
                                            style="
                                                color:var(--text-muted);
                                                font-size:11.5px;
                                            "
                                        >
                                            Pembayaran ditolak
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            style="
                                text-align:center;
                                padding:30px;
                                color:var(--text-muted);
                            "
                        >
                            Belum ada pesanan yang tercatat.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- =====================================================
         PAGINATION
    ====================================================== -->

    <div class="order-pagination-wrap">

        <div
            class="order-pagination-info"
            id="orderPaginationInfo"
        >
            Menampilkan 0–0 dari 0
        </div>


        <div class="order-pagination">

            <button
                type="button"
                id="orderPrevBtn"
                class="order-page-btn"
            >
                ‹ Previous
            </button>


            <div
                id="orderPageNumbers"
                class="order-page-numbers"
            ></div>


            <button
                type="button"
                id="orderNextBtn"
                class="order-page-btn"
            >
                Next ›
            </button>

        </div>


        <div class="order-per-page">

            <span>Per Halaman:</span>

            <select id="orderPerPage">

                <option value="8" selected>
                    8
                </option>

                <option value="10">
                    10
                </option>

                <option value="20">
                    20
                </option>

                <option value="50">
                    50
                </option>

            </select>

        </div>

    </div>

</div>


<!-- =========================================================
     DETAIL MODAL
========================================================= -->

<div
    class="modal-overlay"
    id="detailModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="dmTitle"
>

    <div class="modal">

        <div class="modal-h">

            <h2 id="dmTitle">
                Detail order
            </h2>

        </div>

        <div class="modal-b">

            <div
                class="dl"
                id="dmBody"
            ></div>

        </div>

        <div class="modal-f">

            <button
                type="button"
                class="btn"
                id="dmClose"
            >
                Tutup
            </button>

        </div>

    </div>

</div>


<!-- =========================================================
     DELETE MODAL
========================================================= -->

<div
    class="modal-overlay"
    id="deleteModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="delTitle"
>

    <div class="modal">

        <div class="modal-h">

            <h2 id="delTitle">
                Hapus order?
            </h2>

            <p>
                Order berikut akan dihapus dari daftar:
            </p>

        </div>


        <div class="modal-b">

            <div class="dl">

                <div>

                    <span>
                        Kode order
                    </span>

                    <b id="delCode">
                        -
                    </b>

                </div>

            </div>

        </div>


        <div class="modal-f">

            <button
                type="button"
                class="btn"
                id="delNo"
            >
                Batal
            </button>


            <button
                type="button"
                class="btn btn-yes-danger"
                id="delYes"
            >
                Ya, Hapus
            </button>

        </div>

    </div>

</div>


<script>

/* =========================================================
   DATA DETAIL ORDER
========================================================= */

const orderDetail =
    <?= json_encode(
        $detailJs,
        JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_HEX_APOS
        | JSON_HEX_QUOT
    ) ?>;


/* =========================================================
   ESCAPE HTML
========================================================= */

const esc = s =>
    String(s).replace(
        /[&<>"']/g,
        c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[c])
    );


/* =========================================================
   DETAIL MODAL
========================================================= */

const detailModal =
    document.getElementById('detailModal');


document.querySelectorAll('.js-detail')
.forEach(btn => {

    btn.addEventListener('click', () => {

        const items =
            orderDetail[btn.dataset.id] || [];


        document.getElementById('dmBody')
            .innerHTML = items
            .map(
                ([k, v]) =>
                    '<div>' +
                        '<span>' +
                            esc(k) +
                        '</span>' +

                        '<b>' +
                            esc(v) +
                        '</b>' +

                    '</div>'
            )
            .join('');


        detailModal.classList.add('open');

    });

});


const closeDetail = () => {

    detailModal.classList.remove('open');

};


document
    .getElementById('dmClose')
    .addEventListener(
        'click',
        closeDetail
    );


detailModal.addEventListener(
    'click',
    e => {

        if (e.target === detailModal) {

            closeDetail();

        }

    }
);


/* =========================================================
   DELETE MODAL
========================================================= */

const deleteModal =
    document.getElementById('deleteModal');


let deleteUrl = null;


document.querySelectorAll('.js-delete')
.forEach(btn => {

    btn.addEventListener('click', () => {

        deleteUrl =
            btn.dataset.url;


        document.getElementById('delCode')
            .textContent =
            btn.dataset.code;


        deleteModal.classList.add('open');

    });

});


const closeDelete = () => {

    deleteModal.classList.remove('open');

    deleteUrl = null;

};


document
    .getElementById('delNo')
    .addEventListener(
        'click',
        closeDelete
    );


document
    .getElementById('delYes')
    .addEventListener(
        'click',
        () => {

            if (deleteUrl) {

                window.location.href =
                    deleteUrl;

            }

        }
    );


deleteModal.addEventListener(
    'click',
    e => {

        if (e.target === deleteModal) {

            closeDelete();

        }

    }
);


/* =========================================================
   PAGINATION
========================================================= */

let currentOrderPage = 1;

let orderPerPage = 8;


/* =========================================================
   AMBIL SEMUA ORDER
========================================================= */

function getAllOrderRows() {

    return Array.from(
        document.querySelectorAll(
            '#ordersTableBody .order-row'
        )
    );

}


/* =========================================================
   AMBIL ORDER YANG SESUAI SEARCH + FILTER
========================================================= */

function getFilteredOrderRows() {

    const searchInput =
        document.getElementById(
            'orderSearchInput'
        );


    const statusFilter =
        document.getElementById(
            'statusFilter'
        );


    const search =
        searchInput
            ? searchInput.value
                .trim()
                .toLowerCase()
            : '';


    const status =
        statusFilter
            ? statusFilter.value
            : 'all';


    return getAllOrderRows()
        .filter(row => {

            const text =
                row.innerText
                    .toLowerCase();


            const rowStatus =
                row.dataset.status || '';


            const matchSearch =
                search === ''
                ||
                text.includes(search);


            const matchStatus =
                status === 'all'
                ||
                rowStatus === status;


            return (
                matchSearch
                &&
                matchStatus
            );

        });

}


/* =========================================================
   RENDER PAGINATION
========================================================= */

function renderOrderPagination() {

    /*
     * PENTING:
     * Sekarang yang dihitung adalah
     * hasil FILTER, bukan semua order.
     */

    const allRows =
        getAllOrderRows();


    const filteredRows =
        getFilteredOrderRows();


    const totalData =
        filteredRows.length;


    const totalPages =
        Math.max(
            1,
            Math.ceil(
                totalData /
                orderPerPage
            )
        );


    /* Jangan sampai halaman melebihi jumlah halaman */

    if (
        currentOrderPage >
        totalPages
    ) {

        currentOrderPage =
            totalPages;

    }


    const startIndex =
        (currentOrderPage - 1)
        *
        orderPerPage;


    const endIndex =
        Math.min(
            startIndex +
            orderPerPage,
            totalData
        );


    /* =====================================================
       SEMBUNYIKAN SEMUA ORDER
    ====================================================== */

    allRows.forEach(row => {

        row.style.display =
            'none';

    });


    /* =====================================================
       TAMPILKAN HANYA ORDER HALAMAN AKTIF
    ====================================================== */

    filteredRows
        .slice(
            startIndex,
            endIndex
        )
        .forEach(row => {

            row.style.display =
                'table-row';

        });


    /* =====================================================
       COUNTER SEARCH
    ====================================================== */

    const counter =
        document.getElementById(
            'ordersCounter'
        );


    if (counter) {

        counter.textContent =
            'Menampilkan ' +
            totalData +
            ' orderan';

    }


    /* =====================================================
       INFO PAGINATION
    ====================================================== */

    const info =
        document.getElementById(
            'orderPaginationInfo'
        );


    if (info) {

        if (totalData === 0) {

            info.textContent =
                'Menampilkan 0–0 dari 0';

        } else {

            info.textContent =
                'Menampilkan ' +
                (startIndex + 1) +
                '–' +
                endIndex +
                ' dari ' +
                totalData;

        }

    }


    /* =====================================================
       PREVIOUS
    ====================================================== */

    const prev =
        document.getElementById(
            'orderPrevBtn'
        );


    if (prev) {

        prev.disabled =
            currentOrderPage <= 1;

    }


    /* =====================================================
       NEXT
    ====================================================== */

    const next =
        document.getElementById(
            'orderNextBtn'
        );


    if (next) {

        next.disabled =
            currentOrderPage >=
            totalPages;

    }


    /* =====================================================
       NOMOR HALAMAN
    ====================================================== */

    const numbers =
        document.getElementById(
            'orderPageNumbers'
        );


    if (!numbers) {

        return;

    }


    numbers.innerHTML = '';


    /*
     * Kalau tidak ada data,
     * jangan tampilkan nomor halaman.
     */

    if (totalData === 0) {

        return;

    }


    for (
        let page = 1;
        page <= totalPages;
        page++
    ) {

        const button =
            document.createElement(
                'button'
            );


        button.type =
            'button';


        button.className =
            'order-page-number';


        button.textContent =
            page;


        if (
            page ===
            currentOrderPage
        ) {

            button.classList.add(
                'active'
            );

        }


        button.addEventListener(
            'click',
            () => {

                currentOrderPage =
                    page;


                renderOrderPagination();


                /*
                 * Scroll ke posisi
                 * atas tabel/page.
                 */

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }
        );


        numbers.appendChild(
            button
        );

    }

}


/* =========================================================
   SEARCH / FILTER
========================================================= */

function filterOrders() {

    /*
     * Setiap search/filter berubah,
     * balik ke halaman pertama.
     */

    currentOrderPage = 1;


    renderOrderPagination();

}


/* =========================================================
   SEARCH INPUT
========================================================= */

const orderSearchInput =
    document.getElementById(
        'orderSearchInput'
    );


if (orderSearchInput) {

    orderSearchInput.addEventListener(
        'input',
        filterOrders
    );

}


/* =========================================================
   STATUS FILTER
========================================================= */

const statusFilter =
    document.getElementById(
        'statusFilter'
    );


if (statusFilter) {

    statusFilter.addEventListener(
        'change',
        filterOrders
    );

}


/* =========================================================
   PREVIOUS
========================================================= */

const orderPrevBtn =
    document.getElementById(
        'orderPrevBtn'
    );


if (orderPrevBtn) {

    orderPrevBtn.addEventListener(
        'click',
        () => {

            if (
                currentOrderPage > 1
            ) {

                currentOrderPage--;


                renderOrderPagination();

            }

        }
    );

}


/* =========================================================
   NEXT
========================================================= */

const orderNextBtn =
    document.getElementById(
        'orderNextBtn'
    );


if (orderNextBtn) {

    orderNextBtn.addEventListener(
        'click',
        () => {

            const total =
                getFilteredOrderRows()
                    .length;


            const totalPages =
                Math.max(
                    1,
                    Math.ceil(
                        total /
                        orderPerPage
                    )
                );


            if (
                currentOrderPage <
                totalPages
            ) {

                currentOrderPage++;


                renderOrderPagination();

            }

        }
    );

}


/* =========================================================
   PER HALAMAN
========================================================= */

const orderPerPageSelect =
    document.getElementById(
        'orderPerPage'
    );


if (orderPerPageSelect) {

    orderPerPageSelect.addEventListener(
        'change',
        function () {

            orderPerPage =
                parseInt(
                    this.value,
                    10
                );


            currentOrderPage = 1;


            renderOrderPagination();

        }
    );

}


/* =========================================================
   ESCAPE = TUTUP MODAL
========================================================= */

document.addEventListener(
    'keydown',
    e => {

        if (
            e.key === 'Escape'
        ) {

            closeDetail();

            closeDelete();

        }

    }
);


/* =========================================================
   JALANKAN PAGINATION SAAT LOAD
========================================================= */

renderOrderPagination();

</script>