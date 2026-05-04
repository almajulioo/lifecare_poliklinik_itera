/**
 * LifeCare+ Medication Modal Handler - SIMPLIFIED
 */

function showMedicationModal(medicationData) {
  console.log('[showMedicationModal] Displaying modal with data:', medicationData);

  const med = medicationData;
  const modal = document.createElement('div');
  modal.id = 'medication-modal';
  modal.className = 'fixed inset-0 bg-black/50 flex items-end z-50';
  modal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    align-items: flex-end;
    z-index: 50;
    animation: fadeIn 0.3s ease-out;
  `;

  modal.innerHTML = `
    <div style="
      width: 100%;
      background-color: white;
      border-radius: 24px 24px 0 0;
      padding: 24px;
      max-height: 100vh;
      overflow-y: auto;
      animation: slideUp 0.4s ease-out;
    ">
      <!-- Close Button -->
      <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button onclick="document.getElementById('medication-modal').remove()" style="
          background: none;
          border: none;
          cursor: pointer;
          color: #9ca3af;
          font-size: 24px;
          padding: 0;
        ">
          ✕
        </button>
      </div>

      <!-- Time Display -->
      <div style="text-align: center; margin-bottom: 24px;">
        <div style="font-size: 48px; font-weight: bold; color: #2563eb;">${med.time}</div>
        <div style="font-size: 12px; color: #6b7280; margin-top: 8px;">Waktu Minum Obat</div>
      </div>

      <!-- Medicine Card -->
      <div style="
        background-color: #eff6ff;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        border: 2px solid #bfdbfe;
        display: flex;
        align-items: center;
        gap: 16px;
      ">
        <div style="
          width: 64px;
          height: 64px;
          border-radius: 50%;
          background-color: #bfdbfe;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 32px;
        ">💊</div>
        <div>
          <h2 style="font-size: 18px; font-weight: bold; color: #111827;">${med.medicine_name || 'Obat'}</h2>
          <p style="font-size: 12px; color: #4b5563; margin-top: 4px;">
            ${med.medicine_dose || ''} ${med.medicine_unit || ''}
          </p>
        </div>
      </div>

      <!-- Info Text -->
      <div style="
        background-color: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 16px;
        border-radius: 4px;
        margin-bottom: 24px;
      ">
        <p style="font-size: 12px; color: #374151;">
          <strong>Sudah Waktunya minum obat Anda</strong><br>
          Jangan lupa minum obat sesuai dosis yang telah ditentukan.
        </p>
      </div>

      <!-- Action Buttons -->
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <!-- Confirm Button -->
        <button onclick="
          console.log('Confirm clicked');
          alert('Obat berhasil dicatat!');
          document.getElementById('medication-modal').remove();
        " style="
          width: 100%;
          background-color: #22c55e;
          color: white;
          font-weight: bold;
          padding: 16px;
          border-radius: 9999px;
          border: none;
          cursor: pointer;
          font-size: 14px;
          transition: background-color 0.2s;
        " onmouseover="this.style.backgroundColor='#16a34a'" onmouseout="this.style.backgroundColor='#22c55e'">
          ✓ Saya Sudah Diminum
        </button>

        <!-- Snooze Button -->
        <button onclick="
          console.log('Snooze clicked');
          alert('Pengingat ditunda 5 menit');
          document.getElementById('medication-modal').remove();
        " style="
          width: 100%;
          background-color: #d1d5db;
          color: #1f2937;
          font-weight: bold;
          padding: 12px;
          border-radius: 9999px;
          border: none;
          cursor: pointer;
          font-size: 14px;
          transition: background-color 0.2s;
        " onmouseover="this.style.backgroundColor='#9ca3af'" onmouseout="this.style.backgroundColor='#d1d5db'">
          ⏱ Tunda (15 menit)
        </button>

        <!-- Close Button -->
        <button onclick="document.getElementById('medication-modal').remove()" style="
          width: 100%;
          background-color: #f3f4f6;
          color: #374151;
          font-weight: 600;
          padding: 12px;
          border-radius: 9999px;
          border: none;
          cursor: pointer;
          font-size: 14px;
          transition: background-color 0.2s;
        " onmouseover="this.style.backgroundColor='#e5e7eb'" onmouseout="this.style.backgroundColor='#f3f4f6'">
          Tutup
        </button>
      </div>

      <style>
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes slideUp {
          from {
            transform: translateY(100%);
            opacity: 0;
          }
          to {
            transform: translateY(0);
            opacity: 1;
          }
        }
      </style>
    </div>
  `;

  document.body.appendChild(modal);

  // Close on background click
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.remove();
    }
  });

  console.log('[showMedicationModal] Modal displayed successfully');
}

// Make globally available
window.showMedicationModal = showMedicationModal;

console.log('[MedicationModal] Simple version loaded and ready');
