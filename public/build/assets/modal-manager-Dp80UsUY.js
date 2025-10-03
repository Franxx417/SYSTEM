window.ModalManager={showConfirmation:function(a,t,i,s,l){var n="globalConfirmModal",r=document.getElementById(n);r&&r.remove();var e=`
            <div class="modal fade" id="${n}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${a}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${t}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${s||"Cancel"}</button>
                            <button type="button" class="btn btn-primary" id="confirmAction">${i||"Confirm"}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;document.body.insertAdjacentHTML("beforeend",e);var o=new bootstrap.Modal(document.getElementById(n));document.getElementById("confirmAction").addEventListener("click",function(){l&&l(),o.hide()}),document.getElementById(n).addEventListener("hidden.bs.modal",function(){document.getElementById(n).remove()}),o.show()},showSuccess:function(a,t){this.showAlert("success",a,t)},showError:function(a,t){this.showAlert("danger",a,t)},showInfo:function(a,t){this.showAlert("info",a,t)},showAlert:function(a,t,i){var s="globalAlertModal",l=document.getElementById(s);l&&l.remove();var n={success:'<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle text-success" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>',danger:'<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle text-danger" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/></svg>',info:'<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-info-circle text-info" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>'},r=`
            <div class="modal fade" id="${s}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${t}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    ${n[a]||n.info}
                                </div>
                                <div>
                                    ${i}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;document.body.insertAdjacentHTML("beforeend",r);var e=new bootstrap.Modal(document.getElementById(s));document.getElementById(s).addEventListener("hidden.bs.modal",function(){document.getElementById(s).remove()}),e.show()},validateForm:function(a,t){var i=document.getElementById(a);if(!i)return!1;var s=!0,l=null;return i.querySelectorAll(".is-invalid").forEach(function(n){n.classList.remove("is-invalid")}),i.querySelectorAll(".invalid-feedback").forEach(function(n){n.remove()}),Object.keys(t).forEach(function(n){var r=i.querySelector('[name="'+n+'"]');if(r){var e=t[n],o=r.value.trim(),d=!0,c="";if(e.required&&!o&&(d=!1,c=e.requiredMessage||"This field is required."),d&&e.minLength&&o.length<e.minLength&&(d=!1,c=e.minLengthMessage||`Minimum ${e.minLength} characters required.`),d&&e.maxLength&&o.length>e.maxLength&&(d=!1,c=e.maxLengthMessage||`Maximum ${e.maxLength} characters allowed.`),d&&e.email&&o&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(o)&&(d=!1,c=e.emailMessage||"Please enter a valid email address."),d&&e.number&&o&&isNaN(o)&&(d=!1,c=e.numberMessage||"Please enter a valid number."),d&&e.min!==void 0&&parseFloat(o)<e.min&&(d=!1,c=e.minMessage||`Value must be at least ${e.min}.`),d&&e.custom&&!e.custom(o,i)&&(d=!1,c=e.customMessage||"Invalid value."),!d){s=!1,r.classList.add("is-invalid");var m=document.createElement("div");m.className="invalid-feedback",m.textContent=c,r.parentNode.appendChild(m),l||(l=r)}}}),l&&l.focus(),s},autoDismissAlerts:function(a,t){a=a||".alert",t=t||5e3,document.querySelectorAll(a).forEach(function(i){setTimeout(function(){if(i&&i.parentNode){var s=new bootstrap.Alert(i);s.close()}},t)})}};document.addEventListener("DOMContentLoaded",function(){ModalManager.autoDismissAlerts(),document.addEventListener("submit",function(a){var t=a.target;if(t.hasAttribute("data-validate")){var i={};try{i=JSON.parse(t.getAttribute("data-validate"))}catch(s){console.warn("Invalid validation rules:",s);return}if(!ModalManager.validateForm(t.id,i))return a.preventDefault(),!1}})});
