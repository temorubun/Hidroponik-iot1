// Function to update relative timestamps
function updateRelativeTimestamps() {
    const timestamps = document.querySelectorAll('[data-timestamp]');
    timestamps.forEach(element => {
        const timestamp = element.getAttribute('data-timestamp');
        if (timestamp === 'Just now') {
            element.textContent = 'Just now';
        } else if (timestamp === 'Never') {
            element.textContent = 'Never';
        } else {
            const relativeTime = moment(timestamp).fromNow();
            element.textContent = relativeTime;
        }
    });
}

// Function to update device status using AJAX
function updateDeviceStatus() {
    fetch('/api/device-status')
        .then(response => response.json())
        .then(data => {
            // Update statistics
            document.querySelector('.stats-value[data-stat="total-devices"]').textContent = data.totalDevices;
            document.querySelector('.stats-value[data-stat="online-devices"]').textContent = data.onlineDevices;
            
            // Update device status in table with animation
            if (data.devices) {
                data.devices.forEach(device => {
                    const row = document.querySelector(`#device-${device.id}`);
                    if (row) {
                        const statusBadge = row.querySelector('.status-badge');
                        if (statusBadge) {
                            const newStatus = device.is_online ? 'success' : 'danger';
                            const newText = device.is_online ? 'Online' : 'Offline';
                            
                            if (statusBadge.classList.contains(`bg-${device.is_online ? 'danger' : 'success'}`)) {
                                statusBadge.style.animation = 'none';
                                statusBadge.offsetHeight; // Trigger reflow
                                statusBadge.style.animation = null;
                                statusBadge.className = `badge bg-${newStatus} status-badge`;
                                statusBadge.textContent = newText;
                            }
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Error updating device status:', error));
}

// Handle tab button active state with smooth transitions
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab switching
    const triggerTabList = document.querySelectorAll('button[data-bs-toggle="tab"]');
    triggerTabList.forEach(triggerEl => {
        triggerEl.addEventListener('click', function(event) {
            event.preventDefault();
            const tabTrigger = new bootstrap.Tab(triggerEl);
            tabTrigger.show();
            
            const tabId = triggerEl.getAttribute('id');
            const newTab = tabId === 'devices-tab' ? 'devices' : 'projects';
            
            // Create URL for fetching first page of new tab
            const url = new URL(window.location.href);
            url.searchParams.set('tab', newTab);
            url.searchParams.delete('projects_page');
            url.searchParams.delete('devices_page');
            
            // Fetch the first page content
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update the content of the active tab
                    const activeTabId = newTab === 'devices' ? 'devices' : 'projects';
                    const newContent = doc.querySelector(`#${activeTabId}`);
                    document.querySelector(`#${activeTabId}`).innerHTML = newContent.innerHTML;
                    
                    // Update URL
                    window.history.pushState({}, '', url);
                });
        });
    });

    // Handle pagination clicks
    document.addEventListener('click', function(event) {
        const paginationLink = event.target.closest('a[href*="page"]');
        if (paginationLink) {
            event.preventDefault();
            const url = new URL(paginationLink.href);
            
            // Fetch the new page content
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update the content of the active tab
                    const currentTab = document.querySelector('.tab-pane.active').id;
                    const newContent = doc.querySelector(`#${currentTab}`);
                    document.querySelector(`#${currentTab}`).innerHTML = newContent.innerHTML;
                    
                    // Update URL
                    window.history.pushState({}, '', url);
                });
        }
    });

    // Show initial tab based on URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab === 'devices') {
        const devicesTab = document.querySelector('#devices-tab');
        if (devicesTab) {
            const tabTrigger = new bootstrap.Tab(devicesTab);
            tabTrigger.show();
        }
    }

    // Initial update
    updateRelativeTimestamps();
    
    // Update timestamps every minute
    setInterval(updateRelativeTimestamps, 60000);
    
    // Update status every 30 seconds
    setInterval(updateDeviceStatus, 30000);
}); 