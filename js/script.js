// js/script.js - Client side interactivity for Exam Timetable Scheduling

document.addEventListener('DOMContentLoaded', () => {
  // Auto-dismiss alerts after 4 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.5s ease';
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

  // Table Search Filter
  const tableSearchInput = document.getElementById('tableSearch');
  if (tableSearchInput) {
    tableSearchInput.addEventListener('keyup', (e) => {
      const searchTerm = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('.custom-table tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }

  // Department Filter Select
  const deptFilter = document.getElementById('deptFilter');
  if (deptFilter) {
    deptFilter.addEventListener('change', (e) => {
      const selectedDept = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('.custom-table tbody tr');

      rows.forEach(row => {
        const deptCell = row.querySelector('.dept-col');
        if (!deptCell) return;
        const text = deptCell.textContent.toLowerCase();
        if (!selectedDept || text.includes(selectedDept)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  }
});

// Delete Confirmation
function confirmDelete(message) {
  return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
}

// Trigger Print View
function printTimetable() {
  window.print();
}
