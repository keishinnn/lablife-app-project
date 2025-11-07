document.addEventListener('DOMContentLoaded', () => {
    const userControlModal = document.getElementById('user-control-modal');
    const userReportModal = document.getElementById('user-report-modal');
    const closeUserReportBtn = document.querySelector('.close-user-report-modal-button');
    const nextButtons = document.querySelectorAll('.report-next-button');
    const backButtons = document.querySelectorAll('.report-back-button');
    const reportSubmitBtn = document.getElementById('report-submit-button');
  
    const categoriesContainer = document.getElementById('report-categories-container');
    const reasonsContainer = document.getElementById('report-reasons-container');
    const summaryContainer = document.getElementById('report-summary');
    const otherUserInput = document.getElementById('other_user_id');
    const csrfInput = document.getElementById('csrf_token_input');
  
    let optionsCache = null;
    let selected = {
      categoryId: null,
      categoryName: null,
      reasonId: null,
      reasonText: null
    };
  
    function showModal(modal) {
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    function hideModal(modal) {
      modal.style.display = 'none';
      document.body.style.overflow = '';
    }
    function showStep(step) {
      document.querySelectorAll('.report-step').forEach(el => (el.style.display = 'none'));
      const el = document.querySelector(`.report-step[data-step="${step}"]`);
      if (el) el.style.display = '';
    }
  
    const userControlNextButton = document.querySelector('.select-modal-submit-button');
    if (userControlNextButton) {
      userControlNextButton.addEventListener('click', async () => {
        const selectedOption = document.querySelector('input[name="user-control-type"]:checked');
        if (selectedOption && selectedOption.value === 'report-user') {
          hideModal(userControlModal);
          await openReportModal();
        }
      });
    }
  
    async function openReportModal() {
      showModal(userReportModal);
      await initializeReportFlow();
    }
  
    if (closeUserReportBtn) {
      closeUserReportBtn.addEventListener('click', () => {
        hideModal(userReportModal);
        showStep(1);
      });
    }
  
    async function fetchOptions() {
      if (optionsCache) return optionsCache;
      const res = await fetch('/u/report/fetch-options', { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Failed to fetch report options');
      optionsCache = await res.json();
      return optionsCache;
    }
  
    function renderCategories(categories) {
      categoriesContainer.innerHTML = '';
      categories.forEach(cat => {
        const id = `report-cat-${cat.id}`;
        const wrapper = document.createElement('div');
        wrapper.className = 'report-option';
        wrapper.innerHTML = `
          <input type="radio" id="${id}" name="report-category" value="${cat.id}" data-name="${escapeHtml(cat.name)}">
          <label for="${id}">${escapeHtml(cat.name)}</label>
        `;
        categoriesContainer.appendChild(wrapper);
        wrapper.querySelector('input').addEventListener('change', ev => {
          selected.categoryId = ev.target.value;
          selected.categoryName = ev.target.dataset.name || cat.name;
          selected.reasonId = null;
          selected.reasonText = null;
        });
      });
    }
  
    function renderReasons(reasons) {
      reasonsContainer.innerHTML = '';
      if (!reasons || !reasons.length) {
        reasonsContainer.innerHTML =
          '<p class="info-text">No specific reasons for this category. Continue to review.</p>';
        return;
      }
      reasons.forEach(r => {
        const id = `report-reason-${r.id}`;
        const wrapper = document.createElement('div');
        wrapper.className = 'report-option';
        wrapper.innerHTML = `
          <input type="radio" id="${id}" name="report-reason" value="${r.id}" data-text="${escapeHtml(r.reason)}">
          <label for="${id}">${escapeHtml(r.reason)}</label>
        `;
        reasonsContainer.appendChild(wrapper);
        wrapper.querySelector('input').addEventListener('change', ev => {
          selected.reasonId = ev.target.value;
          selected.reasonText = ev.target.dataset.text || r.reason;
        });
      });
    }
  
    function escapeHtml(s) {
      return s ? s.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])) : '';
    }
  
    async function initializeReportFlow() {
      showStep(1);
      selected = { categoryId: null, categoryName: null, reasonId: null, reasonText: null };
      categoriesContainer.innerHTML = '<p class="loading-text">Loading options...</p>';
      reasonsContainer.innerHTML = '';
      const opts = await fetchOptions();
      renderCategories(opts.categories);
    }
  
    nextButtons.forEach(btn => {
      btn.addEventListener('click', async () => {
        const nextStep = btn.dataset.nextStep;
        if (nextStep === '2') {
          if (!selected.categoryId) {
            alert('Please select a category first.');
            return;
          }
          const reasons = (optionsCache.reasons || []).filter(r => r.category_id === selected.categoryId);
          if (!reasons.length) {
            buildSummary();
            showStep(3); // jump straight to review
            return;
          }
          renderReasons(reasons);
          showStep(2);
        } else if (nextStep === '3') {
          const reasons = (optionsCache.reasons || []).filter(r => r.category_id === selected.categoryId);
          if (reasons.length && !selected.reasonId) {
            alert('Please select a reason first.');
            return;
          }
          buildSummary();
          showStep(3);
        }
      });
    });
  
    backButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const prevStep = btn.dataset.prevStep;
        showStep(prevStep);
      });
    });
  
    function buildSummary() {
      const reasonPart = selected.reasonText
        ? `<div class="summary-row"><strong>Reason:</strong> ${escapeHtml(selected.reasonText)}</div>`
        : '';
      summaryContainer.innerHTML = `
        <div class="summary-row"><strong>Category:</strong> ${escapeHtml(selected.categoryName || '')}</div>
        ${reasonPart}
      `;
    }
  
    if (reportSubmitBtn) {
      reportSubmitBtn.addEventListener('click', async () => {
        reportSubmitBtn.disabled = true;
        reportSubmitBtn.textContent = 'Submitting...';
        const payload = {
          other_user_id: otherUserInput ? otherUserInput.value : null,
          category_id: selected.categoryId,
          reason_id: selected.reasonId
        };
  
        try {
          const res = await fetch('/u/report/submit', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-Token': csrfInput ? csrfInput.value : ''
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin'
          });
  
          const json = await res.json();
          if (json.success) {
            hideModal(userReportModal);
            alert('Report submitted. Thank you.');
            window.location.href = '/u/messages';
          } else throw new Error(json.message || 'Failed to submit report.');
        } catch (err) {
          console.error('Submit error:', err);
          alert('Failed to submit report. Try again later.');
        } finally {
          reportSubmitBtn.disabled = false;
          reportSubmitBtn.textContent = 'Submit Report';
        }
      });
    }
  });
  