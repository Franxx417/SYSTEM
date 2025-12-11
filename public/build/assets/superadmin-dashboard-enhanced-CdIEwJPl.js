class y{constructor(){this.currentTab=this.getCurrentTab(),this.csrfToken=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),this.init()}init(){this.bindEvents(),this.initializeCurrentTab(),this.setupAutoRefresh(),this.setupNotifications()}getCurrentTab(){return new URLSearchParams(window.location.search).get("tab")||"overview"}bindEvents(){document.addEventListener("DOMContentLoaded",()=>{this.setupAlertAutoDismiss(),this.setupFormValidation()}),this.bindOverviewEvents(),this.bindUserManagementEvents(),this.bindSecurityEvents(),this.bindSystemEvents(),this.bindDatabaseEvents(),this.bindLogsEvents(),this.bindBrandingEvents()}bindOverviewEvents(){const e=document.querySelector('[data-action="refresh-metrics"]');e&&e.addEventListener("click",()=>this.refreshSystemMetrics()),this.startAutoRefresh()}startAutoRefresh(){(window.location.search.includes("tab=overview")||!window.location.search.includes("tab="))&&setInterval(()=>{this.refreshSystemMetrics(!0)},3e4)}async refreshSystemMetrics(e=!1){try{const t=await this.makeRequest("/api/superadmin/metrics","GET");t.success&&(this.updateMetricsDisplay(t.data),e||this.showNotification("System metrics refreshed successfully","success"))}catch(t){e||this.showNotification("Failed to refresh metrics","error"),console.error("System metrics refresh failed:",t)}}updateMetricsDisplay(e){Object.keys(e).forEach(t=>{const s=document.querySelector(`[data-metric="${t}"]`);s&&t!=="system_performance"&&(s.textContent=e[t])}),e.system_performance&&this.updateSystemPerformanceMetrics(e.system_performance)}updateSystemPerformanceMetrics(e){const t=document.querySelector('[data-metric="cpu_usage"]');t&&e.cpu&&(t.textContent=`${e.cpu.usage_percent}%`);const s=document.querySelector('[data-metric="memory_usage"]');s&&e.memory&&e.memory.system&&(s.textContent=`${e.memory.system.usage_percent}%`);const a=document.querySelector('[data-metric="disk_usage"]');a&&e.disk&&(a.textContent=`${e.disk.usage_percent}%`);const i=document.querySelector('[data-metric="network_connections"]');i&&e.network&&(i.textContent=e.network.active_connections);const n=document.querySelector(".badge.bg-success, .badge.bg-danger");n&&e.network&&(n.className=`badge bg-${e.network.database_connectivity?"success":"danger"}`,n.textContent=e.network.database_connectivity?"OK":"Error")}bindUserManagementEvents(){document.addEventListener("click",s=>{if(s.target.matches('[data-action="reset-password"]')){const a=s.target.getAttribute("data-user-id");this.resetUserPassword(a)}if(s.target.matches('[data-action="toggle-user"]')){const a=s.target.getAttribute("data-user-id");this.toggleUserStatus(a)}if(s.target.matches('[data-action="delete-user"]')){const a=s.target.getAttribute("data-user-id");this.deleteUser(a)}});const e=document.querySelector("#quick-user-form");e&&e.addEventListener("submit",s=>{s.preventDefault(),this.createUserQuick(new FormData(e))});const t=document.querySelector("#user-search");t&&t.addEventListener("input",s=>{this.filterUsers(s.target.value)})}async resetUserPassword(e){if(confirm("Are you sure you want to reset this user's password?"))try{const t=await this.makeRequest("/api/superadmin/users/reset-password","POST",{user_id:e});t.success?(this.showNotification(`Password reset successfully. New password: ${t.new_password}`,"success"),this.showPasswordModal(t.new_password)):this.showNotification(t.error||"Failed to reset password","error")}catch{this.showNotification("Failed to reset password","error")}}async toggleUserStatus(e){if(confirm("Are you sure you want to toggle this user's status?"))try{const t=await this.makeRequest("/api/superadmin/users/toggle","POST",{user_id:e});t.success?(this.showNotification("User status updated successfully","success"),setTimeout(()=>location.reload(),1e3)):this.showNotification(t.error||"Failed to update user status","error")}catch{this.showNotification("Failed to update user status","error")}}async deleteUser(e){if(confirm("Are you sure you want to delete this user? This action cannot be undone."))try{const t=await this.makeRequest("/api/superadmin/users/delete","DELETE",{user_id:e});t.success?(this.showNotification("User deleted successfully","success"),setTimeout(()=>location.reload(),1e3)):this.showNotification(t.error||"Failed to delete user","error")}catch{this.showNotification("Failed to delete user","error")}}async createUserQuick(e){try{const t=await this.makeRequest("/api/superadmin/users/create","POST",e);t.success?(this.showNotification("User created successfully","success"),document.querySelector("#quick-user-form").reset(),setTimeout(()=>location.reload(),1e3)):this.showNotification(t.error||"Failed to create user","error")}catch{this.showNotification("Failed to create user","error")}}filterUsers(e){const t=document.querySelectorAll(".user-row"),s=e.toLowerCase();t.forEach(a=>{const i=a.querySelector(".user-name")?.textContent.toLowerCase()||"",n=a.querySelector(".user-email")?.textContent.toLowerCase()||"",o=a.querySelector(".user-role")?.textContent.toLowerCase()||"",h=i.includes(s)||n.includes(s)||o.includes(s);a.style.display=h?"":"none"})}bindSecurityEvents(){const e=document.querySelector("#security-settings-form");e&&e.addEventListener("submit",s=>{s.preventDefault(),this.updateSecuritySettings(new FormData(e))});const t=document.querySelector('[data-action="force-logout-all"]');t&&t.addEventListener("click",()=>this.forceLogoutAllSessions())}async updateSecuritySettings(e){try{const t=await this.makeRequest("/api/superadmin/security/update","POST",e);t.success?this.showNotification("Security settings updated successfully","success"):this.showNotification(t.error||"Failed to update security settings","error")}catch{this.showNotification("Failed to update security settings","error")}}async forceLogoutAllSessions(){if(confirm("Are you sure you want to force logout all active sessions? This will log out all users."))try{const e=await this.makeRequest("/api/superadmin/security/force-logout-all","POST");e.success?(this.showNotification("All sessions terminated successfully","success"),setTimeout(()=>location.reload(),2e3)):this.showNotification(e.error||"Failed to terminate sessions","error")}catch{this.showNotification("Failed to terminate sessions","error")}}bindSystemEvents(){document.addEventListener("click",t=>{t.target.matches('[data-action="clear-cache"]')&&this.clearCache(),t.target.matches('[data-action="backup-system"]')&&this.backupSystem(),t.target.matches('[data-action="update-system"]')&&this.updateSystem(),t.target.matches('[data-action="restart-services"]')&&this.restartServices()});const e=document.querySelector('[data-action="refresh-system-info"]');e&&e.addEventListener("click",()=>this.refreshSystemInfo())}async clearCache(){if(confirm("Are you sure you want to clear the system cache?"))try{const e=await this.makeRequest("/api/superadmin/system/clear-cache","POST");e.success?this.showNotification("Cache cleared successfully","success"):this.showNotification(e.error||"Failed to clear cache","error")}catch{this.showNotification("Failed to clear cache","error")}}async backupSystem(){if(confirm("Are you sure you want to create a full system backup? This may take several minutes."))try{this.showNotification("Backup started... This may take a few minutes.","info");const e=await this.makeRequest("/api/superadmin/system/backup","POST");e.success?this.showNotification("System backup completed successfully","success"):this.showNotification(e.error||"Failed to create backup","error")}catch{this.showNotification("Failed to create backup","error")}}async updateSystem(){if(confirm("Are you sure you want to update the system? This will restart the application."))try{this.showNotification("System update started...","info");const e=await this.makeRequest("/api/superadmin/system/update","POST");e.success?(this.showNotification("System updated successfully. Restarting...","success"),setTimeout(()=>location.reload(),3e3)):this.showNotification(e.error||"Failed to update system","error")}catch{this.showNotification("Failed to update system","error")}}async restartServices(){if(confirm("Are you sure you want to restart system services? This may cause temporary downtime."))try{this.showNotification("Restarting services...","info");const e=await this.makeRequest("/api/superadmin/system/restart-services","POST");e.success?this.showNotification("Services restarted successfully","success"):this.showNotification(e.error||"Failed to restart services","error")}catch{this.showNotification("Failed to restart services","error")}}async refreshSystemInfo(){try{const e=await this.makeRequest("/api/superadmin/system/info","GET");e.success&&(this.updateSystemInfoDisplay(e.data),this.showNotification("System information refreshed","success"))}catch{this.showNotification("Failed to refresh system information","error")}}updateSystemInfoDisplay(e){Object.keys(e).forEach(t=>{const s=document.querySelector(`[data-system-info="${t}"]`);s&&(s.textContent=e[t])})}bindDatabaseEvents(){document.addEventListener("click",e=>{e.target.matches('[data-action="optimize-database"]')&&this.optimizeDatabase(),e.target.matches('[data-action="check-integrity"]')&&this.checkDatabaseIntegrity(),e.target.matches('[data-action="repair-tables"]')&&this.repairTables(),e.target.matches('[data-action="create-backup"]')&&this.createDatabaseBackup(),e.target.matches('[data-action="load-table-info"]')&&this.loadTableInfo()}),this.currentTab==="database"&&setTimeout(()=>this.loadTableInfo(),500)}async optimizeDatabase(){if(confirm("Are you sure you want to optimize the database? This may take several minutes."))try{this.showNotification("Optimizing database...","info");const e=await this.makeRequest("/api/superadmin/database/optimize","POST");e.success?this.showNotification("Database optimized successfully","success"):this.showNotification(e.error||"Failed to optimize database","error")}catch{this.showNotification("Failed to optimize database","error")}}async checkDatabaseIntegrity(){try{this.showNotification("Checking database integrity...","info");const e=await this.makeRequest("/api/superadmin/database/check-integrity","POST");e.success?(this.showNotification("Database integrity check completed","success"),this.showDatabaseIntegrityResults(e.data)):this.showNotification(e.error||"Failed to check database integrity","error")}catch{this.showNotification("Failed to check database integrity","error")}}async repairTables(){if(confirm("Are you sure you want to repair database tables? This may take several minutes."))try{this.showNotification("Repairing database tables...","info");const e=await this.makeRequest("/api/superadmin/database/repair","POST");e.success?this.showNotification("Database tables repaired successfully","success"):this.showNotification(e.error||"Failed to repair tables","error")}catch{this.showNotification("Failed to repair tables","error")}}async createDatabaseBackup(){if(confirm("Are you sure you want to create a database backup?"))try{this.showNotification("Creating database backup...","info");const e=await this.makeRequest("/api/superadmin/database/backup","POST");e.success?this.showNotification("Database backup created successfully","success"):this.showNotification(e.error||"Failed to create backup","error")}catch{this.showNotification("Failed to create backup","error")}}async loadTableInfo(){const e=document.getElementById("table-info");if(!e){console.error("[Database] Table info container not found");return}e.innerHTML=`
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted">Loading table information...</div>
            </div>
        `;try{const t=await this.makeRequest("/api/superadmin/database/table-info","GET");if((window.location.hostname==="localhost"||window.location.hostname==="127.0.0.1")&&(console.log("[Database] API Response:",t),console.log("[Database] Data received:",t.data)),t&&t.success&&t.data)this.displayTableInfo(t.data,e);else{const s=t?.error||"Failed to load table information";console.error("[Database] Error:",s),e.innerHTML=`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${s}
                    </div>
                `}}catch(t){console.error("[Database] Load table info failed:",t),e.innerHTML=`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> Failed to load table information
                    <div class="mt-2"><small>${t.message||"Unknown error occurred"}</small></div>
                </div>
            `}}displayTableInfo(e,t){if(!e||!Array.isArray(e)){console.error("[Database] Invalid table data:",e),t.innerHTML='<div class="alert alert-warning">No table data available</div>';return}if(e.length===0){t.innerHTML='<div class="alert alert-info">No tables found in database</div>';return}(window.location.hostname==="localhost"||window.location.hostname==="127.0.0.1")&&console.log("[Database] Displaying",e.length,"tables");let s=`
            <table class="table table-sm table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Table Name</th>
                        <th class="text-center">Records</th>
                        <th class="text-center">Columns</th>
                        <th class="text-center">Size</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;e.forEach((o,h)=>{const l=this.sanitizeHTML(o.name||"Unknown"),b=o.count!==null&&o.count!==void 0?typeof o.count=="number"?o.count:"Error":0,r=o.columns!==null&&o.columns!==void 0?typeof o.columns=="number"?o.columns:typeof o.columns=="object"&&Array.isArray(o.columns)?o.columns.length:0:0,c=o.size||"N/A",d=o.status||"Unknown",u=o.error||null;let m="";switch(d.toLowerCase()){case"ok":m='<span class="badge bg-success">OK</span>';break;case"missing":m='<span class="badge bg-warning">Missing</span>';break;case"error":m=`<span class="badge bg-danger" title="${this.sanitizeHTML(u||"Unknown error")}">Error</span>`;break;default:m=`<span class="badge bg-secondary">${d}</span>`}const p=u||typeof b!="number"?`<span class="badge bg-danger" title="${this.sanitizeHTML(u||"Error retrieving count")}">Error</span>`:`<span class="badge bg-info">${b.toLocaleString()}</span>`,g=typeof r=="number"?`<span class="badge bg-secondary">${r}</span>`:'<span class="badge bg-warning">N/A</span>';s+=`
                <tr>
                    <td><strong>${l}</strong></td>
                    <td class="text-center">${p}</td>
                    <td class="text-center">${g}</td>
                    <td class="text-center"><span class="text-muted">${this.sanitizeHTML(c)}</span></td>
                    <td class="text-center">${m}</td>
                    <td class="text-center">
                        <button 
                            class="btn btn-sm btn-outline-primary" 
                            onclick="superadminDashboard.viewTableDetails('${l}')" 
                            ${u||d.toLowerCase()==="error"||d.toLowerCase()==="missing"?"disabled":""}
                            title="${u||d.toLowerCase()==="error"?"Cannot view details due to error":"View table details"}">
                            <i class="fas fa-info-circle me-1"></i>View Details
                        </button>
                    </td>
                </tr>
            `}),s+="</tbody></table>";const a=e.length,i=e.filter(o=>o.status&&o.status.toLowerCase()==="ok").length,n=e.filter(o=>o.status&&(o.status.toLowerCase()==="error"||o.status.toLowerCase()==="missing")).length;s+=`
            <div class="mt-3 p-3 bg-light rounded">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="h5 mb-0">${a}</div>
                        <div class="text-muted small">Total Tables</div>
                    </div>
                    <div class="col-md-4">
                        <div class="h5 mb-0 text-success">${i}</div>
                        <div class="text-muted small">Healthy</div>
                    </div>
                    <div class="col-md-4">
                        <div class="h5 mb-0 ${n>0?"text-danger":"text-muted"}">${n}</div>
                        <div class="text-muted small">Issues</div>
                    </div>
                </div>
            </div>
        `,t.innerHTML=s}async viewTableDetails(e){try{this.showNotification("Loading table details...","info");const t=await this.makeRequest(`/api/superadmin/database/table-details/${e}`,"GET");t.success&&t.data?this.showTableDetailsModal(e,t.data):this.showNotification(t.error||"Failed to load table details","error")}catch(t){console.error("Failed to load table details:",t),this.showNotification("Failed to load table details: "+t.message,"error")}}showDatabaseIntegrityResults(e){this.createModal("Database Integrity Check Results",this.formatIntegrityResults(e)).show()}formatIntegrityResults(e){let t='<div class="table-responsive"><table class="table table-sm">';return t+="<thead><tr><th>Table</th><th>Status</th><th>Issues</th></tr></thead><tbody>",e.forEach(s=>{const a=s.status==="OK"?"success":"danger";t+=`
                <tr>
                    <td>${s.table}</td>
                    <td><span class="badge bg-${a}">${s.status}</span></td>
                    <td>${s.issues||"None"}</td>
                </tr>
            `}),t+="</tbody></table></div>",t}bindLogsEvents(){document.addEventListener("click",s=>{s.target.matches('[data-action="refresh-logs"]')&&this.refreshLogs(),s.target.matches('[data-action="clear-logs"]')&&this.clearLogs()});const e=document.getElementById("log-level-filter");e&&e.addEventListener("change",s=>{this.filterLogs(s.target.value)});const t=document.getElementById("log-settings-form");t&&t.addEventListener("submit",s=>{s.preventDefault(),this.updateLogSettings(new FormData(t))}),this.currentTab==="logs"&&setTimeout(()=>this.refreshLogs(),500)}async refreshLogs(){const e=document.getElementById("logs-container");if(e){e.innerHTML='<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading logs...</div>';try{const t=await this.makeRequest("/api/superadmin/logs/recent","GET");t.success?(this.displayLogs(t.data.logs,e),this.updateLogStats(t.data.stats)):e.innerHTML=`<div class="alert alert-danger">${t.error||"Failed to load logs"}</div>`}catch{e.innerHTML='<div class="alert alert-danger">Failed to load logs</div>'}}}displayLogs(e,t){if(!e||e.length===0){t.innerHTML='<div class="text-center text-muted py-4">No log entries found</div>';return}let s='<div class="log-entries">';e.forEach(a=>{const i=this.getLogLevelClass(a.level);s+=`
                <div class="log-entry border-start border-3 border-${i} p-3 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-${i} me-2">${a.level.toUpperCase()}</span>
                                <small class="text-muted">${a.timestamp}</small>
                            </div>
                            <div class="log-message">${a.message}</div>
                            ${a.context?`<div class="log-context mt-2"><small class="text-muted">Context: ${JSON.stringify(a.context)}</small></div>`:""}
                        </div>
                    </div>
                </div>
            `}),s+="</div>",t.innerHTML=s}getLogLevelClass(e){return{error:"danger",warning:"warning",info:"info",debug:"secondary"}[e.toLowerCase()]||"secondary"}updateLogStats(e){Object.keys(e).forEach(t=>{const s=document.querySelector(`[data-log-stat="${t}"]`);s&&(s.textContent=e[t])})}filterLogs(e){document.querySelectorAll(".log-entry").forEach(s=>{if(!e)s.style.display="";else{const a=s.querySelector(".badge"),i=a?a.textContent.toLowerCase():"";s.style.display=i===e?"":"none"}})}async clearLogs(){if(confirm("Are you sure you want to clear all logs? This action cannot be undone."))try{const e=await this.makeRequest("/api/superadmin/logs/clear","POST");e.success?(this.showNotification("Logs cleared successfully","success"),this.refreshLogs()):this.showNotification(e.error||"Failed to clear logs","error")}catch{this.showNotification("Failed to clear logs","error")}}async updateLogSettings(e){try{const t=await this.makeRequest("/api/superadmin/logs/settings","POST",e);t.success?this.showNotification("Log settings updated successfully","success"):this.showNotification(t.error||"Failed to update log settings","error")}catch{this.showNotification("Failed to update log settings","error")}}bindBrandingEvents(){const e=document.querySelector('input[name="logo"]');e&&e.addEventListener("change",s=>{this.previewLogo(s.target.files[0])});const t=document.querySelector("#branding-form");t&&t.addEventListener("submit",s=>{s.preventDefault(),this.updateBranding(new FormData(t))})}previewLogo(e){if(!e)return;const t=new FileReader;t.onload=s=>{let a=document.querySelector("#logo-preview");a||(a=document.createElement("div"),a.id="logo-preview",a.className="mt-2",document.querySelector('input[name="logo"]').parentNode.appendChild(a)),a.innerHTML=`<img src="${s.target.result}" alt="Logo Preview" style="height:48px;width:auto" class="border rounded">`},t.readAsDataURL(e)}async updateBranding(e){try{const t=await this.makeRequest("/api/superadmin/branding/update","POST",e);t.success?(this.showNotification("Branding updated successfully","success"),setTimeout(()=>location.reload(),1e3)):this.showNotification(t.error||"Failed to update branding","error")}catch{this.showNotification("Failed to update branding","error")}}async makeRequest(e,t="GET",s=null){const a={method:t,headers:{"X-CSRF-TOKEN":this.csrfToken,"X-Requested-With":"XMLHttpRequest"}};s&&(s instanceof FormData?a.body=s:(a.headers["Content-Type"]="application/json",a.body=JSON.stringify(s)));try{const i=await fetch(e,a);if(!i.ok)throw new Error(`HTTP ${i.status}: ${i.statusText}`);return await i.json()}catch(i){throw console.error("Request failed:",i),i}}showNotification(e,t="info"){const s=this.sanitizeHTML(e),a=document.createElement("div");a.className=`alert alert-${t==="error"?"danger":t} alert-dismissible fade show position-fixed`,a.style.cssText="top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;",a.innerHTML=`
            ${s}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `,document.body.appendChild(a),setTimeout(()=>{a.parentNode&&a.remove()},5e3)}sanitizeHTML(e){if(e==null)return"";const t=document.createElement("div");return t.textContent=String(e),t.innerHTML}safeGet(e,t,s=null){try{return t.split(".").reduce((a,i)=>a&&a[i],e)??s}catch{return s}}formatNumber(e,t="N/A"){if(e==null||e==="")return t;const s=Number(e);return isNaN(s)?t:s.toLocaleString()}showPasswordModal(e){this.createModal("Password Reset",`
            <div class="alert alert-warning">
                <strong>New Password:</strong> 
                <code class="fs-5">${e}</code>
            </div>
            <p>Please save this password securely. The user should change it upon first login.</p>
        `).show()}showTableDetailsModal(e,t){const s="table-details-modal-"+Date.now(),a=t.columns&&t.columns.length>0?`
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover">
                    <thead class="sticky-top bg-light">
                        <tr>
                            <th>Column Name</th>
                            <th>Data Type</th>
                            <th>Nullable</th>
                            <th>Identity</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${t.columns.map(r=>`
                            <tr>
                                <td><strong>${r.name}</strong></td>
                                <td><code class="text-primary">${r.type}</code></td>
                                <td><span class="badge bg-${r.nullable==="YES"?"warning":"success"}">${r.nullable}</span></td>
                                <td><span class="badge bg-${r.identity==="YES"?"info":"secondary"}">${r.identity}</span></td>
                                <td><small class="text-muted">${r.default}</small></td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `:'<p class="text-muted text-center py-3">No column information available</p>',i=t.indexes&&t.indexes.length>0?`
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Index Name</th>
                            <th>Type</th>
                            <th>Unique</th>
                            <th>Primary</th>
                            <th>Columns</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${t.indexes.map(r=>`
                            <tr>
                                <td><strong>${r.name}</strong></td>
                                <td><span class="badge bg-secondary">${r.type}</span></td>
                                <td><span class="badge bg-${r.unique==="YES"?"success":"secondary"}">${r.unique}</span></td>
                                <td><span class="badge bg-${r.primary==="YES"?"primary":"secondary"}">${r.primary}</span></td>
                                <td><small>${r.columns.join(", ")}</small></td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `:'<p class="text-muted text-center py-3">No indexes found</p>',n=t.foreign_keys&&t.foreign_keys.length>0?`
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Constraint Name</th>
                            <th>Column</th>
                            <th>References</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${t.foreign_keys.map(r=>`
                            <tr>
                                <td><strong>${r.name}</strong></td>
                                <td><code>${r.column}</code></td>
                                <td><code class="text-success">${r.references}</code></td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `:'<p class="text-muted text-center py-3">No foreign key constraints found</p>',o=t.sample_data&&t.sample_data.length>0?`
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover">
                    <thead class="sticky-top bg-light">
                        <tr>
                            ${Object.keys(t.sample_data[0]).map(r=>`<th>${r}</th>`).join("")}
                        </tr>
                    </thead>
                    <tbody>
                        ${t.sample_data.map(r=>`
                            <tr>
                                ${Object.values(r).map(c=>`<td><small>${c===null?'<em class="text-muted">NULL</em>':typeof c=="object"?JSON.stringify(c):String(c).length>100?String(c).substring(0,100)+"...":c}</small></td>`).join("")}
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        `:'<p class="text-muted text-center py-3">No sample data available</p>',h=`
            <div class="modal fade" id="${s}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-table me-2"></i>Table Details: <strong>${e}</strong>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Table Summary -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Records</h6>
                                            <h3 class="mb-0 text-primary">${t.count||0}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Columns</h6>
                                            <h3 class="mb-0 text-success">${t.columns?.length||0}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Size</h6>
                                            <h3 class="mb-0 text-info">${t.size||"N/A"}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Engine</h6>
                                            <h3 class="mb-0 text-warning" style="font-size: 1.2rem;">${t.engine||"N/A"}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabbed Content -->
                            <ul class="nav nav-tabs" id="tableDetailsTabs-${s}" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="columns-tab-${s}" data-bs-toggle="tab" 
                                            data-bs-target="#columns-${s}" type="button" role="tab">
                                        <i class="bi bi-list-columns-reverse me-1"></i>Columns (${t.columns?.length||0})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="indexes-tab-${s}" data-bs-toggle="tab" 
                                            data-bs-target="#indexes-${s}" type="button" role="tab">
                                        <i class="bi bi-key me-1"></i>Indexes (${t.indexes?.length||0})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="fk-tab-${s}" data-bs-toggle="tab" 
                                            data-bs-target="#fk-${s}" type="button" role="tab">
                                        <i class="bi bi-link-45deg me-1"></i>Foreign Keys (${t.foreign_keys?.length||0})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="data-tab-${s}" data-bs-toggle="tab" 
                                            data-bs-target="#data-${s}" type="button" role="tab">
                                        <i class="bi bi-table me-1"></i>Sample Data (${t.sample_data?.length||0})
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content border border-top-0 p-3" id="tableDetailsTabContent-${s}">
                                <div class="tab-pane fade show active" id="columns-${s}" role="tabpanel">
                                    ${a}
                                </div>
                                <div class="tab-pane fade" id="indexes-${s}" role="tabpanel">
                                    ${i}
                                </div>
                                <div class="tab-pane fade" id="fk-${s}" role="tabpanel">
                                    ${n}
                                </div>
                                <div class="tab-pane fade" id="data-${s}" role="tabpanel">
                                    ${o}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;document.body.insertAdjacentHTML("beforeend",h);const l=document.getElementById(s);l.addEventListener("hidden.bs.modal",()=>{l.remove()}),new bootstrap.Modal(l).show()}createModal(e,t){const s="dynamic-modal-"+Date.now(),a=`
            <div class="modal fade" id="${s}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${e}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${t}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;document.body.insertAdjacentHTML("beforeend",a);const i=document.getElementById(s);return i.addEventListener("hidden.bs.modal",()=>{i.remove()}),new bootstrap.Modal(i)}setupAlertAutoDismiss(){document.querySelectorAll(".alert:not(.alert-permanent)").forEach(t=>{setTimeout(()=>{t&&t.parentNode&&t.remove()},5e3)})}setupFormValidation(){document.querySelectorAll("form[data-validate]").forEach(t=>{t.addEventListener("submit",s=>{this.validateForm(t)||s.preventDefault()})})}validateForm(e){let t=!0;return e.querySelectorAll("[required]").forEach(a=>{a.value.trim()?a.classList.remove("is-invalid"):(a.classList.add("is-invalid"),t=!1)}),t}initializeCurrentTab(){switch(this.currentTab){case"database":setTimeout(()=>this.loadTableInfo(),500);break;case"overview":setTimeout(()=>this.refreshSystemMetrics(),500);break;case"logs":setTimeout(()=>this.loadRecentLogs(),500);break}}setupAutoRefresh(){this.currentTab==="overview"&&setInterval(()=>{this.refreshSystemMetrics()},3e4)}setupNotifications(){typeof io<"u"&&io().on("admin-notification",t=>{this.showNotification(t.message,t.type)})}async loadRecentLogs(){const e=document.getElementById("logs-container");if(e){e.innerHTML='<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading logs...</div>';try{const t=await this.makeRequest("/api/superadmin/logs/recent","GET");t.logs?this.displayLogs(t.logs,e):e.innerHTML='<div class="alert alert-danger">Failed to load logs</div>'}catch{e.innerHTML='<div class="alert alert-danger">Failed to load logs</div>'}}}displayLogs(e,t){if(!e||e.length===0){t.innerHTML='<div class="text-center text-muted py-4">No log entries found</div>';return}let s='<div class="log-entries">';e.forEach(a=>{const i=this.extractLogLevel(a),n=this.getLogLevelClass(i);s+=`
                <div class="log-entry mb-2 p-2 border-start border-3 border-${n} bg-light">
                    <div class="small text-muted">${this.formatLogTimestamp(a)}</div>
                    <div class="log-content">${this.escapeHtml(a)}</div>
                </div>
            `}),s+="</div>",t.innerHTML=s}extractLogLevel(e){const t=e.match(/\.(ERROR|WARNING|INFO|DEBUG)\]/);return t?t[1].toLowerCase():"info"}getLogLevelClass(e){return{error:"danger",warning:"warning",info:"info",debug:"secondary"}[e]||"info"}formatLogTimestamp(e){const t=e.match(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/);return t?t[1]:"Unknown time"}escapeHtml(e){const t=document.createElement("div");return t.textContent=e,t.innerHTML}}document.addEventListener("DOMContentLoaded",()=>{try{window.superadminDashboard=new y,console.log("SuperadminDashboard initialized successfully")}catch(f){console.error("Failed to initialize SuperadminDashboard:",f)}});
