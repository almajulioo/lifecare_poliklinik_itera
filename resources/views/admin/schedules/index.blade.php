@extends('admin.layouts.app')

@section('title', 'Jadwal Obat')

@section('content')
<div class="p-8">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
            <strong>✓ Sukses!</strong> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
            <strong>✗ Error!</strong> {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Manajemen Jadwal Obat</h1>
        <a href="{{ route('admin.schedules.create') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
            + Buat Jadwal Baru
        </a>
    </div>

    <!-- Search Box -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex gap-3">
            <input
                type="text"
                name="search"
                placeholder="Cari pasien berdasarkan nama atau ID..."
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
            />
            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition"
            >
                 Cari
            </button>
            @if(request('search'))
                <a
                    href="{{ route('admin.schedules.index') }}"
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-semibold transition"
                >
                    ✕ Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Statistics Bar -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Total Pasien</p>
            <p class="text-2xl font-bold text-blue-600">{{ $users->total() }}</p>
        </div>
        @php
            $totalSchedules = 0;
            $activeCount = 0;
            foreach($users as $user) {
                foreach($user->medicationSchedules as $schedule) {
                    $totalSchedules++;
                    if($schedule->is_active) $activeCount++;
                }
            }
        @endphp
        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Jadwal Aktif</p>
            <p class="text-2xl font-bold text-green-600">{{ $activeCount }}</p>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">Total Jadwal</p>
            <p class="text-2xl font-bold text-orange-600">{{ $totalSchedules }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($users->count() > 0)
            <table class="w-full text-sm">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">No</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Nama Pasien</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-900">Obat yang Diminum</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $userIndex => $user)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ ($users->currentPage() - 1) * 10 + $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">ID: {{ $user->id }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @if($user->medicationSchedules->count() > 0)
                                        @forelse($user->medicationSchedules->unique('medicine_id') as $schedule)
                                            <div class="text-sm text-gray-900">
                                                {{ $schedule->medicine->name ?? 'N/A' }} ({{ $schedule->medicine->dose ?? 0 }} {{ $schedule->medicine->unit ?? '' }})
                                            </div>
                                        @empty
                                            <span class="text-gray-500">—</span>
                                        @endforelse
                                    @else
                                        <span class="text-gray-500">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <!-- View Icon (Eye) -->
                                    <button 
                                        type="button"
                                        onclick="showScheduleModal({{ $user->id }})"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded transition"
                                        title="Lihat jadwal lengkap">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <!-- Delete Icon -->
                                    <form action="#" method="POST" class="inline deleteForm" data-user-id="{{ $user->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="button"
                                            onclick="deleteScheduleForUser(this, {{ $user->id }})"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded transition"
                                            title="Hapus jadwal">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-white border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-gray-500 text-lg">Belum ada pasien dengan jadwal obat.</p>
                <a href="{{ route('admin.schedules.create') }}" class="text-blue-600 hover:text-blue-800 font-semibold mt-2 inline-block">
                    Buat jadwal sekarang →
                </a>
            </div>
        @endif
    </div>

    <!-- Schedule Details Modal -->
    <div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-auto">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[calc(100vh-6rem)] overflow-y-auto m-auto my-8">
            <div class="sticky top-0 bg-gray-50 px-6 py-4 border-b flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Jadwal Pasien</h3>
                    <button id="editSelectedBtn" onclick="editSelected()" disabled class="ml-2 px-3 py-1 text-sm bg-orange-100 text-orange-800 rounded disabled:opacity-50 inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-7l7 7"></path>
                        </svg>
                        Edit Jadwal
                    </button>
                    <button id="deleteSelectedBtn" onclick="deleteSelected()" disabled class="ml-2 px-3 py-1 text-sm bg-red-100 text-red-800 rounded disabled:opacity-50 inline-flex items-center" title="Hapus jadwal terpilih">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3"></path>
                        </svg>
                        Hapus
                    </button>
                </div>
                <button onclick="closeScheduleModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modalContent" class="px-6 py-4">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
function showScheduleModal(userId) {
    // Fetch schedule data via AJAX
    fetch(`/admin/schedules?user_id=${userId}&ajax=1`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        let html = `<div class="space-y-4">`;
        
        data.schedules.forEach((schedule, idx) => {
            html += `<div data-schedule-id="${schedule.id}" onclick="selectSchedule(${schedule.id}, this)" class="schedule-card border-l-4 border-blue-500 pl-4 py-3 bg-gray-50 rounded cursor-pointer hover:shadow-sm">
                <div class="font-semibold text-gray-900">${schedule.medicine.name} - ${schedule.medicine.dose} ${schedule.medicine.unit}</div>
                <div class="text-sm text-gray-600 mt-2 space-y-1">
                    <p><strong>Jam:</strong> ${schedule.time || '—'}</p>
                    <p><strong>Mulai:</strong> ${schedule.start_date}</p>
                    <p><strong>Berakhir:</strong> ${schedule.end_date || 'Ongoing'}</p>
                    <p><strong>Sumber:</strong> ${schedule.source}</p>
                    <p><strong>Status:</strong> <span class="px-2 py-1 rounded text-xs ${schedule.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                        ${schedule.is_active ? '✓ Aktif' : '✗ Nonaktif'}
                    </span></p>
                </div>
                <div class="flex gap-2 mt-3">
                    <a href="/admin/schedules/${schedule.id}/edit" class="text-sm px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600">Edit</a>
                    <button type="button" onclick="deleteSchedule(${schedule.id})" class="text-sm px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Hapus</button>
                </div>
            </div>`;
        });
        
        html += `</div>`;
        
        document.getElementById('modalContent').innerHTML = html;
        // reset selection
        selectedScheduleId = null;
        selectedEl = null;
        const editBtn = document.getElementById('editSelectedBtn');
        if (editBtn) editBtn.disabled = true;
        
        document.getElementById('scheduleModal').classList.remove('hidden');
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal memuat jadwal');
    });
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}

function editScheduleForUser(userId) {
    // Redirect to create new schedule for this user
    window.location.href = `/admin/schedules/create?user_id=${userId}`;
}

let selectedScheduleId = null;
let selectedEl = null;

function selectSchedule(id, el) {
    // remove previous highlight
    if (selectedEl && selectedEl !== el) {
        selectedEl.classList.remove('ring-2', 'ring-blue-300', 'bg-white');
    }
    selectedScheduleId = id;
    selectedEl = el;
    el.classList.add('ring-2', 'ring-blue-300', 'bg-white');
    const editBtn = document.getElementById('editSelectedBtn');
    if (editBtn) editBtn.disabled = false;
    const delBtn = document.getElementById('deleteSelectedBtn');
    if (delBtn) delBtn.disabled = false;
}

function editSelected() {
    if (!selectedScheduleId) return;
    window.location.href = `/admin/schedules/${selectedScheduleId}/edit`;
}

function deleteSelected() {
    if (!selectedScheduleId) return;
    if (!confirm('Hapus jadwal yang dipilih? Tindakan ini tidak bisa dibatalkan.')) return;
    // reuse existing deleteSchedule which performs confirmation and request
    deleteSchedule(selectedScheduleId);
}

function deleteScheduleForUser(button, userId) {
    if (!confirm(`Hapus semua jadwal untuk pasien ini? Tindakan ini tidak bisa dibatalkan.`)) {
        return;
    }
    
    // Get all schedule IDs for this user first, then delete them all
    fetch(`/admin/schedules?user_id=${userId}&ajax=1`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.schedules || data.schedules.length === 0) {
            alert('Tidak ada jadwal untuk dihapus');
            return;
        }
        
        // Delete all schedules for this user
        let deleteCount = 0;
        data.schedules.forEach(schedule => {
            fetch(`/admin/schedules/${schedule.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    deleteCount++;
                    if (deleteCount === data.schedules.length) {
                        alert(`${deleteCount} jadwal berhasil dihapus`);
                        location.reload();
                    }
                }
            });
        });
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menghapus jadwal');
    });
}

function deleteSchedule(scheduleId) {
    if (!confirm('Hapus jadwal ini?')) {
        return;
    }
    
    fetch(`/admin/schedules/${scheduleId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            // Close modal and reload
            closeScheduleModal();
            location.reload();
        } else {
            alert('Gagal menghapus jadwal');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menghapus jadwal: ' + err);
    });
}
</script>
@endsection