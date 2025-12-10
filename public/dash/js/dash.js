// ==========================================
// GLOBAL VARIABLES & STATE MANAGEMENT
// ==========================================
let isModalOpen = false;
let isLoading = false;
let hasMore = true;

// ==========================================
// SINGLE EVENT LISTENER FOR HEATMAP CLICKS
// ==========================================
document.addEventListener("DOMContentLoaded", function () {
    console.log("Dashboard initialized");
    
    // Load initial heatmap
    loadHeatmap();
    
    // Filter event listeners
    document.getElementById("heatmapProjectFilter").addEventListener("change", loadHeatmap);
    
    // SINGLE event listener for heatmap clicks
    document.addEventListener("click", handleHeatmapClick);
});

// ==========================================
// HEATMAP CLICK HANDLER
// ==========================================
function handleHeatmapClick(e) {
    // Only handle heatmap-click elements
    if (!e.target.classList.contains("heatmap-click")) return;
    
    // Prevent multiple clicks
    e.preventDefault();
    e.stopPropagation();
    
    const sheetId = e.target.dataset.sheet;
    const status = e.target.dataset.status;
    
    if (!sheetId || !status) {
        console.error("Missing sheetId or status");
        return;
    }
    
    console.log(`Opening QA items: Sheet ${sheetId}, Status ${status}`);
    
    // Close existing modal if open
    if (isModalOpen) {
        closeQaPopup();
        setTimeout(() => {
            createQaModal(sheetId, status);
        }, 50);
    } else {
        createQaModal(sheetId, status);
    }
}

// ==========================================
// LOAD HEATMAP FUNCTION
// ==========================================
function loadHeatmap() {
    const projectId = document.getElementById("heatmapProjectFilter").value || "";
    const phaseId = document.getElementById("heatmapPhaseFilter").value || "";
    const tbody = document.getElementById('sheetHeatmapBody');
    
    // Show loading
    tbody.innerHTML = `
        <tr>
            <td colspan="12" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                Loading heatmap...
            </td>
        </tr>`;
    
    fetch(`/analytics/sheet-heatmap?project_id=${projectId}&phase_id=${phaseId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.rows || data.rows.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            No data available for selected filters.
                        </td>
                    </tr>`;
                return;
            }
            
            // Update header
            const headerPhase = data.rows[0]?.phase || "-";
            const headerProject = data.rows[0]?.project || "-";
            
            document.getElementById("heatmapHeaderInfo").innerHTML = 
                `Phase: <span class="text-primary">${headerPhase}</span> → 
                 Project: <span class="text-primary">${headerProject}</span>`;
            
            // Clear and rebuild table
            tbody.innerHTML = "";
            
            data.rows.forEach(row => {
                let statusCells = "";
                
                if (data.statuses && Array.isArray(data.statuses)) {
                    data.statuses.forEach(st => {
                        const val = row[st] ?? 0;
                        const bg = val > 0 ? "rgba(37,99,235,0.9)" : "transparent";
                        const color = val > 0 ? "white" : "#333";
                        
                        statusCells += `
                            <td class="text-center">
                                <span class="badge heatmap-click"
                                    data-sheet="${row.sheet_id}"
                                    data-status="${st}"
                                    style="cursor:pointer; background:${bg}; color:${color}; min-width:32px;">
                                    ${val}
                                </span>
                            </td>`;
                    });
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td>${row.sheet_label || "-"}</td>
                        <td class="text-center fw-bold">${row.total || 0}</td>
                        ${statusCells}
                        <td class="text-center text-primary fw-bold">${row.a_count || 0}</td>
                        <td class="text-center text-info fw-bold">${row.i_count || 0}</td>
                        <td class="text-center text-success fw-bold">${row.c_count || 0}</td>
                    </tr>`;
            });
            
            console.log(`Heatmap loaded: ${data.rows.length} rows`);
        })
        .catch(err => {
            console.error("Heatmap error:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center text-danger py-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading heatmap data
                    </td>
                </tr>`;
        });
}

// ==========================================
// MODAL FUNCTIONS
// ==========================================
function createQaModal(sheetId, status) {
    isModalOpen = true;
    
    // Create modal elements
    const modalContainer = document.createElement('div');
    modalContainer.id = 'qaPopupModal';
    modalContainer.className = 'qa-popup-modal';
    
    const overlay = document.createElement('div');
    overlay.className = 'qa-popup-overlay';
    
    // Modal HTML
    modalContainer.innerHTML = `
        <div class="qa-popup-content">
            <div class="qa-popup-header">
                <div class="qa-popup-title">
                    <i class="fas fa-tasks me-2"></i>
                    QA Items — <span id="popupStatus">${status.toUpperCase()}</span>
                    <span id="popupCount" class="ms-2 badge bg-light text-dark" style="font-size: 0.8rem;"></span>
                </div>
                <button type="button" class="qa-popup-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="qa-popup-body" id="popupBody">
                <div id="popupLoading" class="qa-popup-loading">
                    <div class="spinner-border text-primary" style="width:3rem; height:3rem;"></div>
                    <p class="mt-3 text-muted fw-semibold">Loading items…</p>
                </div>
                
                <div id="popupItemsContainer" class="qa-popup-items d-none">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:70px;">ID</th>
                                    <th>Description</th>
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
                    
                    <div id="popupLoadMore" class="qa-popup-load-more d-none">
                        <button id="loadMoreBtn">
                            <i class="fas fa-chevron-down me-1"></i> Load More Items
                        </button>
                    </div>
                    
                    <div id="infiniteLoading" class="qa-popup-infinite-loading d-none">
                        <div class="spinner-border spinner-border-sm text-secondary me-2"></div>
                        Loading more items...
                    </div>
                </div>
            </div>
            
            <div class="qa-popup-footer d-none" id="popupFooter">
                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                    <small class="text-muted">
                        Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> items
                    </small>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="scrollTopBtn">
                            <i class="fas fa-arrow-up"></i> Top
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="scrollBottomBtn">
                            <i class="fas fa-arrow-down"></i> Bottom
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add to document
    document.body.appendChild(overlay);
    document.body.appendChild(modalContainer);
    
    // Show with animation
    setTimeout(() => {
        overlay.classList.add('show');
        modalContainer.classList.add('show');
    }, 10);
    
    // Store data
    modalContainer.dataset.sheetId = sheetId;
    modalContainer.dataset.status = status;
    modalContainer.dataset.currentPage = 1;
    modalContainer.dataset.totalItems = 0;
    
    // Setup event listeners for modal
    setupModalEvents(modalContainer, overlay);
    
    // Load data
    loadPopupData(sheetId, status, 1);
}

function setupModalEvents(modal, overlay) {
    // Close button
    const closeBtn = modal.querySelector('.qa-popup-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            isModalOpen = false;
            closeQaPopup();
        });
    }
    
    // Overlay click to close
    overlay.addEventListener('click', () => {
        isModalOpen = false;
        closeQaPopup();
    });
    
    // Scroll buttons
    const scrollTopBtn = modal.querySelector('#scrollTopBtn');
    const scrollBottomBtn = modal.querySelector('#scrollBottomBtn');
    
    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', () => {
            const popupBody = document.getElementById('popupBody');
            if (popupBody) popupBody.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    
    if (scrollBottomBtn) {
        scrollBottomBtn.addEventListener('click', () => {
            const popupBody = document.getElementById('popupBody');
            if (popupBody) popupBody.scrollTo({ top: popupBody.scrollHeight, behavior: 'smooth' });
        });
    }
    
    // Load more button
    const loadMoreBtn = modal.querySelector('#loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMoreItems);
    }
    
    // Infinite scroll
    const popupBody = document.getElementById('popupBody');
    if (popupBody) {
        popupBody.addEventListener('scroll', handlePopupScroll);
    }
}

function closeQaPopup() {
    isModalOpen = false;
    isLoading = false;
    hasMore = true;
    
    const modal = document.getElementById('qaPopupModal');
    const overlay = document.querySelector('.qa-popup-overlay');
    
    if (modal) {
        // Remove scroll event listener
        const popupBody = modal.querySelector('#popupBody');
        if (popupBody) {
            popupBody.removeEventListener('scroll', handlePopupScroll);
        }
        modal.remove();
    }
    
    if (overlay) overlay.remove();
    
    document.body.style.overflow = 'auto';
}

// ==========================================
// DATA LOADING FUNCTIONS
// ==========================================
function loadPopupData(sheetId, status, page = 1) {
    const tbody = document.getElementById('popupItemsBody');
    const loading = document.getElementById('popupLoading');
    const container = document.getElementById('popupItemsContainer');
    const loadMoreDiv = document.getElementById('popupLoadMore');
    
    if (!tbody || !loading || !container) return;
    
    // Reset
    tbody.innerHTML = '';
    isLoading = false;
    
    if (loadMoreDiv) {
        loadMoreDiv.classList.add('d-none');
    }
    
    loading.classList.remove('d-none');
    container.classList.add('d-none');
    
    fetch(`/analytics/qa-items?sheet_id=${sheetId}&status=${status}&page=${page}`)
        .then(res => res.json())
        .then(data => {
            const modal = document.getElementById('qaPopupModal');
            if (modal) {
                modal.dataset.totalItems = data.total || 0;
            }
            
            if (!data.items || !Array.isArray(data.items) || data.items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i><br>
                            <h5 class="mt-2">No QA items found</h5>
                            <p class="small">No items match the selected criteria</p>
                        </td>
                    </tr>`;
                hasMore = false;
            } else {
                appendItemsToTable(data.items);
                
                // Check if we should show load more button
                const itemsPerPage = 20;
                const totalLoaded = page * itemsPerPage;
                hasMore = totalLoaded < (data.total || 0);
                
                if (hasMore && loadMoreDiv) {
                    loadMoreDiv.classList.remove('d-none');
                } else if (loadMoreDiv) {
                    loadMoreDiv.classList.add('d-none');
                }
            }
            
            loading.classList.add('d-none');
            container.classList.remove('d-none');
            updateCounters();
            
            setTimeout(adjustModalHeight, 100);
        })
        .catch(err => {
            console.error("Error loading items:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger py-5">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i><br>
                        <h5 class="mt-2">Error loading QA items</h5>
                        <p class="small">${err.message || 'Please try again later'}</p>
                        <button onclick="loadPopupData('${sheetId}', '${status}', ${page})" 
                                class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-redo me-1"></i> Retry
                        </button>
                    </td>
                </tr>`;
            
            loading.classList.add('d-none');
            container.classList.remove('d-none');
        });
}

function handlePopupScroll(e) {
    const container = e.target;
    const modal = document.getElementById('qaPopupModal');
    
    if (!container || !modal || isLoading || !hasMore) return;
    
    const currentPage = parseInt(modal.dataset.currentPage);
    if (currentPage === 1) return;
    
    const scrollBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
    
    if (scrollBottom < 100) {
        loadMoreItems();
    }
}

function loadMoreItems() {
    const modal = document.getElementById('qaPopupModal');
    if (!modal || isLoading) return;
    
    const sheetId = modal.dataset.sheetId;
    const status = modal.dataset.status;
    const currentPage = parseInt(modal.dataset.currentPage) + 1;
    
    const infiniteLoading = document.getElementById('infiniteLoading');
    if (infiniteLoading) {
        infiniteLoading.classList.remove('d-none');
    }
    
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.disabled = true;
    }
    
    isLoading = true;
    
    fetch(`/analytics/qa-items?sheet_id=${sheetId}&status=${status}&page=${currentPage}`)
        .then(res => res.json())
        .then(data => {
            if (data.items && data.items.length > 0) {
                modal.dataset.currentPage = currentPage;
                modal.dataset.totalItems = data.total || 0;
                
                appendItemsToTable(data.items);
                updateCounters();
                
                const itemsPerPage = 20;
                const totalLoaded = currentPage * itemsPerPage;
                hasMore = totalLoaded < (data.total || 0);
                
                if (!hasMore) {
                    const loadMoreDiv = document.getElementById('popupLoadMore');
                    if (loadMoreDiv) {
                        loadMoreDiv.classList.add('d-none');
                    }
                }
            } else {
                hasMore = false;
                const loadMoreDiv = document.getElementById('popupLoadMore');
                if (loadMoreDiv) {
                    loadMoreDiv.classList.add('d-none');
                }
            }
        })
        .catch(err => {
            console.error("Error loading more items:", err);
        })
        .finally(() => {
            isLoading = false;
            if (infiniteLoading) {
                infiniteLoading.classList.add('d-none');
            }
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
            }
        });
}

function appendItemsToTable(items) {
    const tbody = document.getElementById('popupItemsBody');
    if (!tbody) return;
    
    items.forEach(item => {
        const statusClass = getStatusClass(item.status);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.id || "-"}</td>
            <td>${item.description || "No description"}</td>
            <td>${item.sheet_label || "-"}</td>
            <td>
                <span class="status-badge ${statusClass}">
                    ${item.status || "-"}
                </span>
            </td>
            <td>${item.assignee || "-"}</td>
            <td>${item.due_date || "-"}</td>
            <td>${item.created_at || "-"}</td>
            <td class="text-center">
                <a href="/qa_reviews/${item.id}" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye"></i> View
                </a>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function getStatusClass(status) {
    const statusParam = document.getElementById('popupStatus')?.textContent?.trim() || '';
    if (statusParam === 'A') return "status-A";
    if (statusParam === 'I') return "status-I";
    if (statusParam === 'C') return "status-C";
    
    const st = (status || "").toLowerCase();
    
    const statusMap = {
        "open": "status-open",
        "in_progress": "status-in_progress",
        "in-progress": "status-in_progress",
        "needs_info": "status-needs_info",
        "needs-info": "status-needs_info",
        "resolved": "status-resolved",
        "verified": "status-verified",
        "closed": "status-closed"
    };
    
    return statusMap[st] || "status-open";
}

function updateCounters() {
    const modal = document.getElementById('qaPopupModal');
    if (!modal) return;
    
    const tbody = document.getElementById('popupItemsBody');
    const currentCount = tbody ? tbody.children.length : 0;
    const totalCount = modal.dataset.totalItems || currentCount;
    
    document.getElementById('showingCount').textContent = currentCount;
    document.getElementById('totalCount').textContent = totalCount;
    document.getElementById('popupCount').textContent = `${currentCount} items`;
    
    const footer = document.getElementById('popupFooter');
    if (footer) {
        footer.classList[currentCount > 0 ? 'remove' : 'add']('d-none');
    }
}

function adjustModalHeight() {
    const modal = document.getElementById('qaPopupModal');
    const body = document.getElementById('popupBody');
    const itemsContainer = document.getElementById('popupItemsContainer');
    
    if (!modal || !body || !itemsContainer) return;
    
    const viewportHeight = window.innerHeight;
    const modalHeaderHeight = modal.querySelector('.qa-popup-header')?.offsetHeight || 70;
    const modalFooterHeight = modal.querySelector('.qa-popup-footer')?.offsetHeight || 0;
    const contentHeight = itemsContainer.scrollHeight;
    
    const maxBodyHeight = Math.min(
        contentHeight,
        viewportHeight * 0.8 - modalHeaderHeight - modalFooterHeight - 48
    );
    
    body.style.maxHeight = `${maxBodyHeight}px`;
    
    const tableResponsive = itemsContainer.querySelector('.table-responsive');
    if (tableResponsive) {
        tableResponsive.style.maxHeight = `${maxBodyHeight - 100}px`;
    }
}

// Handle window resize
window.addEventListener('resize', adjustModalHeight);
