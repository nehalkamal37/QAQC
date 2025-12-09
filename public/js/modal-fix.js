// Function to create and show popup modal
function showQaItemsPopup(sheetId, status) {
    // إنشاء العناصر الأساسية للمودال
    const modalContainer = document.createElement('div');
    modalContainer.id = 'qaPopupModal';
    modalContainer.className = 'qa-popup-modal';
    
    // إنشاء الـ Overlay
    const overlay = document.createElement('div');
    overlay.className = 'qa-popup-overlay';
    
    // إنشاء محتوى المودال
    modalContainer.innerHTML = `
        <div class="qa-popup-content">
            <!-- HEADER -->
            <div class="qa-popup-header">
                <div class="qa-popup-title">
                    <i class="fas fa-tasks me-2"></i>
                    QA Items — <span id="popupStatus" class="ms-1">${status.toUpperCase()}</span>
                </div>
                <button type="button" class="qa-popup-close" onclick="closeQaPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- BODY -->
            <div class="qa-popup-body">
                <!-- LOADING -->
                <div id="popupLoading" class="qa-popup-loading">
                    <div class="spinner-border text-primary" style="width:3rem; height:3rem;"></div>
                    <p class="mt-3 text-muted fw-semibold">Loading items…</p>
                </div>
                
                <!-- ITEMS TABLE -->
                <div id="popupItemsContainer" class="qa-popup-items d-none">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width:70px;">ID</th>
                                    <th style="width:35%;">Description</th>
                                    <th>Sheet</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Created</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="popupItemsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // إضافة العناصر إلى body
    document.body.appendChild(overlay);
    document.body.appendChild(modalContainer);
    
    // إظهار المودال
    setTimeout(() => {
        overlay.classList.add('show');
        modalContainer.classList.add('show');
    }, 10);
    
    // جلب البيانات
    loadPopupData(sheetId, status);
}

// Function to close popup
function closeQaPopup() {
    const modal = document.getElementById('qaPopupModal');
    const overlay = document.querySelector('.qa-popup-overlay');
    
    if (modal) {
        modal.classList.remove('show');
        overlay.classList.remove('show');
        
        setTimeout(() => {
            if (modal.parentNode) modal.parentNode.removeChild(modal);
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 300);
    }
}

// Function to load data
function loadPopupData(sheetId, status) {
    const tbody = document.getElementById('popupItemsBody');
    const loading = document.getElementById('popupLoading');
    const container = document.getElementById('popupItemsContainer');
    
    // Reset
    tbody.innerHTML = '';
    loading.classList.remove('d-none');
    container.classList.add('d-none');
    
    // Fetch data
    fetch(`/analytics/qa-items?sheet_id=${sheetId}&status=${status}`)
        .then(res => res.json())
        .then(items => {
            if (!Array.isArray(items) || items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            No QA items found.
                        </td>
                    </tr>
                `;
            } else {
                items.forEach(item => {
                    const st = (item.status || "").toLowerCase();
                    
                    let statusClass = {
                        "open": "status-open",
                        "in_progress": "status-in_progress",
                        "in-progress": "status-in_progress",
                        "needs_info": "status-needs_info",
                        "needs-info": "status-needs_info",
                        "resolved": "status-resolved",
                        "verified": "status-verified",
                        "closed": "status-closed"
                    }[st] || "status-open";

                    // A / I / C override
                    if (status === "A") statusClass = "status-A";
                    if (status === "I") statusClass = "status-I";
                    if (status === "C") statusClass = "status-C";

                    tbody.innerHTML += `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.description || "No description"}</td>
                            <td>${item.sheet_label || "-"}</td>
                            <td>
                                <span class="status-badge ${statusClass}">
                                    ${item.status || status}
                                </span>
                            </td>
                            <td>${item.assignee || "-"}</td>
                            <td>${item.due_date || "-"}</td>
                            <td>${item.created_at}</td>
                            <td class="text-center">
                                <a href="/qa_reviews/${item.id}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }
            
            loading.classList.add('d-none');
            container.classList.remove('d-none');
        })
        .catch(err => {
            console.error("Error loading items:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                        Error loading QA items.
                    </td>
                </tr>
            `;
            loading.classList.add('d-none');
            container.classList.remove('d-none');
        });
}

// Close on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQaPopup();
    }
});

// Close on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('qa-popup-overlay')) {
        closeQaPopup();
    }
});