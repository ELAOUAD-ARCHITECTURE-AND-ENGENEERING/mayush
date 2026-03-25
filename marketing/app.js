// Global state
let contentData = [];
let activePhase = 'all';
let openItems = new Set();
let viewMode = 'cards';
let showNewTaskModal = false;
let isRefreshing = false;
let refreshError = false;
let lastRefreshClick = 0;
let refreshRetryTimer = null;
const REF_DEBOUNCE_MS = 300;
const REF_TIMEOUT_MS = 3000;
let editingTaskId = null;

// Phase information
const phaseInfo = {
    1: { name: 'TEASING', color: 'bg-red-500', goal: 'Build mystery, curiosity, emotional anticipation' },
    2: { name: 'LAUNCH DAY', color: 'bg-orange-500', goal: 'Maximum visibility, traffic, first orders' },
    3: { name: 'VIRAL DOMINATION', color: 'bg-green-500', goal: 'Reach, shares, saves, daily conversions' },
    4: { name: 'TRUST & SCALING', color: 'bg-blue-500', goal: 'Remove hesitation, scale sales, build loyalty' }
};

// Initialize app
async function init() {
    await loadData();
    subscribeToChanges();
    renderApp();
}

// Load data from Supabase
async function loadData() {
    try {
        if (!window.supabaseClient || !supabaseClient.from) {
            throw new Error('Supabase client not initialized');
        }
        const { data, error } = await supabaseClient
            .from('content_tasks')
            .select('*')
            .order('id', { ascending: true });

        if (error) throw error;
        
        contentData = data;
        console.log('✅ Loaded', contentData.length, 'tasks from database');
    } catch (error) {
        console.error('❌ Error loading data:', error);
        alert('Error loading data. Check console for details.');
    }
}

// Toggle task completion
async function toggleComplete(taskId, currentStatus) {
    try {
        const { data, error } = await supabaseClient
            .from('content_tasks')
            .update({ 
                is_completed: !currentStatus,
                completed_at: !currentStatus ? new Date().toISOString() : null
            })
            .eq('id', taskId)
            .select();

        if (error) throw error;

        // Update local data
        const index = contentData.findIndex(task => task.id === taskId);
        if (index !== -1) {
            contentData[index] = data[0];
        }

        renderApp();
        console.log('✅ Task updated:', taskId);
    } catch (error) {
        console.error('❌ Error updating task:', error);
        alert('Error updating task. Check console for details.');
    }
}

// Add note to task
async function addNote(taskId) {
    const note = prompt('Add a note for this task:');
    if (note === null) return;

    try {
        const { data, error } = await supabaseClient
            .from('content_tasks')
            .update({ notes: note })
            .eq('id', taskId)
            .select();

        if (error) throw error;

        const index = contentData.findIndex(task => task.id === taskId);
        if (index !== -1) {
            contentData[index] = data[0];
        }

        renderApp();
    } catch (error) {
        console.error('❌ Error adding note:', error);
    }
}

// Export to CSV
function exportToCSV() {
    const headers = ['Phase', 'Day', 'Type', 'Description', 'Team', 'Dimensions', 'Duration', 'Objective', 'Status', 'Notes'];
    const rows = contentData.map(item => [
        `Phase ${item.phase}`,
        item.day,
        item.content_type,
        item.description,
        item.team,
        item.dimensions,
        item.duration,
        item.objective,
        item.is_completed ? 'Completed' : 'Pending',
        item.notes || ''
    ]);

    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'MAYUSH_Calendar_Export.csv';
    link.click();
}

// Calculate statistics
function getStats() {
    const completed = contentData.filter(t => t.is_completed).length;
    const total = contentData.length;
    const video = contentData.filter(t => t.team === 'Video').length;
    const graphic = contentData.filter(t => t.team === 'Graphic').length;
    const social = contentData.filter(t => t.team === 'Social').length;
    const progress = total ? Math.round((completed / total) * 100) : 0;

    return { completed, total, video, graphic, social, progress };
}

async function updateTask(taskId, updates) {
    try {
        const { data, error } = await supabaseClient
            .from('content_tasks')
            .update(updates)
            .eq('id', taskId)
            .select();

        if (error) throw error;

        const index = contentData.findIndex(t => t.id === taskId);
        if (index !== -1 && data && data[0]) {
            contentData[index] = data[0];
        }
        renderApp();
    } catch (error) {
        console.error('❌ Error updating task:', error);
        alert('Error updating task. Check console for details.');
    }
}

async function saveTaskEdits(taskId) {
    const notesEl = document.getElementById(`edit-notes-${taskId}`);
    const descEl = document.getElementById(`edit-desc-${taskId}`);
    const objEl = document.getElementById(`edit-obj-${taskId}`);
    const dimEl = document.getElementById(`edit-dim-${taskId}`);
    const durEl = document.getElementById(`edit-dur-${taskId}`);
    const phaseEl = document.getElementById(`edit-phase-${taskId}`);
    const dayEl = document.getElementById(`edit-day-${taskId}`);
    const typeEl = document.getElementById(`edit-type-${taskId}`);
    const teamEl = document.getElementById(`edit-team-${taskId}`);

    const updates = {
        notes: notesEl ? notesEl.value : null,
        description: descEl ? descEl.value : null,
        objective: objEl ? objEl.value : null,
        dimensions: dimEl ? dimEl.value : null,
        duration: durEl ? durEl.value : null,
        phase: phaseEl ? parseInt(phaseEl.value) : null,
        day: dayEl ? parseInt(dayEl.value) : null,
        content_type: typeEl ? typeEl.value : null,
        team: teamEl ? teamEl.value : null
    };

    Object.keys(updates).forEach(k => updates[k] === null && delete updates[k]);
    await updateTask(taskId, updates);
}

async function refreshData() {
    await loadData();
    renderApp();
}

function refreshClick(e) {
    if (isRefreshing) return;
    const now = Date.now();
    if (now - lastRefreshClick < REF_DEBOUNCE_MS) return;
    lastRefreshClick = now;
    triggerRefresh();
}

async function triggerRefresh() {
    isRefreshing = true;
    refreshError = false;
    renderApp();
    try {
        await loadData();
        isRefreshing = false;
        refreshError = false;
        if (refreshRetryTimer) { clearTimeout(refreshRetryTimer); refreshRetryTimer = null; }
        renderApp();
    } catch (err) {
        console.error('Refresh failed:', err);
        refreshError = true;
        isRefreshing = false;
        renderApp();
        if (refreshRetryTimer) { clearTimeout(refreshRetryTimer); }
        refreshRetryTimer = setTimeout(() => {
            refreshError = false;
            renderApp();
        }, REF_TIMEOUT_MS);
    }
}

function refreshKey(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        refreshClick(e);
    }
}

window.addEventListener('beforeunload', () => {
    if (refreshRetryTimer) clearTimeout(refreshRetryTimer);
});

function subscribeToChanges() {
    try {
        if (!window.supabaseClient || !supabaseClient.channel) {
            throw new Error('Supabase realtime not available');
        }
        supabaseClient
            .channel('public:content_tasks')
            .on('postgres_changes', { event: '*', schema: 'public', table: 'content_tasks' }, payload => {
                const row = payload.new || payload.old;
                if (payload.eventType === 'INSERT') {
                    contentData.push(payload.new);
                } else if (payload.eventType === 'UPDATE') {
                    const idx = contentData.findIndex(t => t.id === row.id);
                    if (idx !== -1) contentData[idx] = payload.new;
                } else if (payload.eventType === 'DELETE') {
                    contentData = contentData.filter(t => t.id !== row.id);
                }
                renderApp();
            })
            .subscribe();
    } catch (e) {
        console.error('Realtime subscription failed', e);
    }
}

function toggleOpen(taskId) {
    if (openItems.has(taskId)) {
        openItems.delete(taskId);
    } else {
        openItems.add(taskId);
    }
    renderApp();
}

function setViewMode(mode) {
    viewMode = mode;
    renderApp();
}

function openNewTaskModal() {
    showNewTaskModal = true;
    renderApp();
}

function closeNewTaskModal() {
    showNewTaskModal = false;
    renderApp();
}

async function deleteTask(taskId) {
    try {
        const { error } = await supabaseClient
            .from('content_tasks')
            .delete()
            .eq('id', taskId);
        if (error) throw error;
        contentData = contentData.filter(t => t.id !== taskId);
        renderApp();
    } catch (e) {
        console.error('❌ Error deleting task:', e);
        alert('Error deleting task. Check console for details.');
    }
}

async function createTask() {
    const phaseEl = document.getElementById('new-phase');
    const dayEl = document.getElementById('new-day');
    const typeEl = document.getElementById('new-type');
    const teamEl = document.getElementById('new-team');
    const dimEl = document.getElementById('new-dim');
    const durEl = document.getElementById('new-dur');
    const objEl = document.getElementById('new-obj');
    const descEl = document.getElementById('new-desc');
    const notesEl = document.getElementById('new-notes');

    const payload = {
        phase: parseInt(phaseEl.value) || 1,
        day: parseInt(dayEl.value) || 1,
        content_type: typeEl.value || '',
        team: teamEl.value || 'Video',
        dimensions: dimEl.value || '',
        duration: durEl.value || '',
        objective: objEl.value || '',
        description: descEl.value || '',
        notes: notesEl.value || '',
        is_completed: false,
        completed_at: null
    };

    try {
        const { data, error } = await supabaseClient
            .from('content_tasks')
            .insert([payload])
            .select();
        if (error) throw error;
        if (data && data[0]) contentData.push(data[0]);
        showNewTaskModal = false;
        renderApp();
    } catch (e) {
        console.error('❌ Error creating task:', e);
        alert('Error creating task. Check console for details.');
    }
}

function openEditModal(taskId) {
    editingTaskId = taskId;
    renderApp();
}

function closeEditModal() {
    editingTaskId = null;
    renderApp();
}

async function saveEditModalTask() {
    const id = editingTaskId;
    if (!id) return;
    const phaseEl = document.getElementById(`editm-phase-${id}`);
    const dayEl = document.getElementById(`editm-day-${id}`);
    const typeEl = document.getElementById(`editm-type-${id}`);
    const teamEl = document.getElementById(`editm-team-${id}`);
    const dimEl = document.getElementById(`editm-dim-${id}`);
    const durEl = document.getElementById(`editm-dur-${id}`);
    const objEl = document.getElementById(`editm-obj-${id}`);
    const descEl = document.getElementById(`editm-desc-${id}`);
    const notesEl = document.getElementById(`editm-notes-${id}`);

    const updates = {
        phase: phaseEl ? parseInt(phaseEl.value) : null,
        day: dayEl ? parseInt(dayEl.value) : null,
        content_type: typeEl ? typeEl.value : null,
        team: teamEl ? teamEl.value : null,
        dimensions: dimEl ? dimEl.value : null,
        duration: durEl ? durEl.value : null,
        objective: objEl ? objEl.value : null,
        description: descEl ? descEl.value : null,
        notes: notesEl ? notesEl.value : null
    };
    Object.keys(updates).forEach(k => updates[k] === null && delete updates[k]);
    await updateTask(id, updates);
    editingTaskId = null;
}

// Render the entire app
function renderApp() {
    const stats = getStats();
    const filteredData = activePhase === 'all' 
        ? contentData 
        : contentData.filter(item => item.phase === parseInt(activePhase));
    const editItem = editingTaskId ? contentData.find(t => t.id === editingTaskId) : null;

    const appHTML = `
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="min-w-0">
                    <h1 class="heading-xl font-bold text-gray-900 mb-2 break-any">MAYUSH DESIGN</h1>
                    <p class="subheading text-gray-600 break-any">30-Day Launch Content Calendar (Full Stack)</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    
                    <button onclick="openNewTaskModal()" class="btn-compact flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors shadow-md">➕ Add Task</button>
                    <button id="refresh-btn" role="button" aria-label="Refresh data" tabindex="0" onclick="refreshClick(event)" onkeydown="refreshKey(event)" ${isRefreshing ? 'disabled' : ''} class="btn-modern refresh-btn ${isRefreshing ? 'loading' : ''} ${refreshError ? 'error' : ''}">
                        <span class="refresh-content ${isRefreshing ? 'fade-out' : 'fade-in'}">🔄</span>
                        <span class="refresh-label ${isRefreshing ? 'fade-out' : 'fade-in'}">Refresh</span>
                        <span class="refresh-spinner ${isRefreshing ? 'fade-in' : 'fade-out'}" aria-hidden="true"></span>
                    </button>
                    <button onclick="exportToCSV()" class="btn-compact flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors shadow-md">
                        📥 Export CSV
                    </button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid stats-grid gap-4 mt-6">
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-xl">
                    <span class="stat-label text-purple-800 font-medium">Total Content</span>
                    <p class="stat-value font-bold text-purple-900">${stats.total}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-4 rounded-xl">
                    <span class="stat-label text-emerald-800 font-medium">Completed</span>
                    <p class="stat-value font-bold text-emerald-900">${stats.completed}</p>
                    <div class="mt-2 bg-emerald-200 rounded-full h-2">
                        <div class="bg-emerald-600 h-2 rounded-full transition-all duration-300" style="width: ${stats.progress}%"></div>
                    </div>
                    <p class="text-xs sm:text-[0.8rem] text-emerald-700 mt-1">${stats.progress}% Complete</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-xl">
                    <span class="stat-label text-red-800 font-medium">Video</span>
                    <p class="stat-value font-bold text-red-900">${stats.video}</p>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-xl">
                    <span class="stat-label text-green-800 font-medium">Graphic</span>
                    <p class="stat-value font-bold text-green-900">${stats.graphic}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-xl">
                    <span class="stat-label text-blue-800 font-medium">Social</span>
                    <p class="stat-value font-bold text-blue-900">${stats.social}</p>
                </div>
            </div>
        </div>

        <!-- Phase Filter -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <div class="flex gap-2 flex-wrap">
                <button onclick="setPhase('all')" class="px-4 py-2 rounded-lg font-medium transition-colors ${activePhase === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700'}">
                    All Phases
                </button>
                ${Object.entries(phaseInfo).map(([key, info]) => `
                    <button onclick="setPhase('${key}')" class="px-4 py-2 rounded-lg font-medium transition-colors ${activePhase === key ? info.color + ' text-white' : 'bg-gray-200 text-gray-700'}">
                        Phase ${key}: ${info.name}
                    </button>
                `).join('')}
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="block md:hidden responsive-cards">
            <div class="space-y-3">
                ${filteredData.map(item => `
                    <div class="bg-white rounded-xl shadow p-4 ${item.is_completed ? 'ring-1 ring-green-200' : ''}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" ${item.is_completed ? 'checked' : ''}
                                       onchange="toggleComplete(${item.id}, ${item.is_completed})"
                                       class="w-5 h-5 text-green-600 rounded cursor-pointer">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white ${phaseInfo[item.phase].color}">P${item.phase}</span>
                                        <span class="text-sm font-semibold">Day ${item.day}</span>
                                    </div>
                                    <div class="text-sm ${item.is_completed ? 'line-through text-gray-500' : 'text-gray-900'}">${item.content_type}</div>
                                </div>
                            </div>
                            <button onclick="toggleOpen(${item.id})" class="text-blue-600 hover:text-blue-800 font-medium">
                                ${openItems.has(item.id) ? 'Hide Details' : 'View Details'}
                            </button>
                        </div>
                            <div class="transition-all duration-300 ${openItems.has(item.id) ? 'mt-3' : ''}" style="max-height: ${openItems.has(item.id) ? '1000px' : '0px'}; overflow: hidden;">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Phase</label>
                                    <select id="edit-phase-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                                        ${[1,2,3,4].map(p => `<option value="${p}" ${p===item.phase? 'selected': ''}>${p}</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Day</label>
                                    <input type="number" id="edit-day-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${item.day || ''}">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Type</label>
                                    <input id="edit-type-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${item.content_type || ''}">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Team</label>
                                    <select id="edit-team-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                                        ${['Video','Graphic','Social'].map(t => `<option value="${t}" ${t===item.team? 'selected': ''}>${t}</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Dimensions</label>
                                    <input id="edit-dim-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${item.dimensions || ''}">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Duration</label>
                                    <input id="edit-dur-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${item.duration || ''}">
                                </div>
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Objective</label>
                                    <textarea id="edit-obj-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="2">${item.objective || ''}</textarea>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Description</label>
                                    <textarea id="edit-desc-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="3">${item.description || ''}</textarea>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="edit-notes-${item.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="2">${item.notes || ''}</textarea>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between gap-2">
                                <button onclick="deleteTask(${item.id})" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Delete</button>
                                <button onclick="refreshData()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">Refresh</button>
                                <button onclick="saveTaskEdits(${item.id})" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Save</button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="hidden md:block bg-white rounded-xl shadow-lg overflow-hidden sticky-header">
            <div class="overflow-x-auto responsive-table">
                <table class="w-full" aria-label="Content tasks table">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-center">✓</th>
                            <th class="px-4 py-3 text-left">Phase</th>
                            <th class="px-4 py-3 text-left">Day</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Team</th>
                            <th class="px-4 py-3 text-left">Dimensions</th>
                            <th class="px-4 py-3 text-left">Duration</th>
                            <th class="px-4 py-3 text-left">Objective</th>
                            <th class="px-4 py-3 text-center">Notes</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${filteredData.map(item => `
                            <tr class="hover:bg-gray-50 transition-colors ${item.is_completed ? 'bg-green-50' : ''}">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" ${item.is_completed ? 'checked' : ''} 
                                           onchange="toggleComplete(${item.id}, ${item.is_completed})"
                                           aria-label="Toggle complete for task ${item.id}"
                                           class="w-6 h-6 text-green-600 rounded cursor-pointer">
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white ${phaseInfo[item.phase].color}">
                                        P${item.phase}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold">${item.day}</td>
                                <td class="px-4 py-3">
                                    <span class="${item.is_completed ? 'line-through text-gray-500' : 'text-gray-900'}">${item.content_type}</span>
                                </td>
                                <td class="px-4 py-3 text-sm cell-desc ${item.is_completed ? 'text-gray-500' : 'text-gray-600'}" title="${item.description}">${item.description}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-medium ${
                                        item.team === 'Video' ? 'bg-red-100 text-red-800' :
                                        item.team === 'Graphic' ? 'bg-green-100 text-green-800' :
                                        'bg-blue-100 text-blue-800'
                                    }">${item.team}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">${item.dimensions}</td>
                                <td class="px-4 py-3 text-sm">${item.duration}</td>
                                <td class="px-4 py-3 text-sm font-medium ${item.is_completed ? 'text-gray-500' : 'text-blue-600'}">${item.objective}</td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="addNote(${item.id})" class="touch-button text-blue-600 hover:text-blue-800 focus-visible:ring-2 focus-visible:ring-blue-300 rounded-md px-2 py-1" title="Add note" aria-label="Add note for task ${item.id}">
                                        ${item.notes ? '📝' : '➕'}
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <button aria-label="Edit task ${item.id}" title="Edit" onclick="openEditModal(${item.id})" class="touch-button inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 focus-visible:ring-2 focus-visible:ring-blue-300">
                                            ✎
                                        </button>
                                        <button aria-label="Delete task ${item.id}" title="Delete" onclick="deleteTask(${item.id})" class="touch-button inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 focus-visible:ring-2 focus-visible:ring-red-300">
                                            🗑
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>

        ${showNewTaskModal ? `
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Add New Task</h2>
                    <button onclick="closeNewTaskModal()" class="text-gray-600 hover:text-gray-900">✕</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Phase</label>
                        <select id="new-phase" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                            ${[1,2,3,4].map(p => `<option value="${p}">${p}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Day</label>
                        <input type="number" id="new-day" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type</label>
                        <input id="new-type" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Team</label>
                        <select id="new-team" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                            ${['Video','Graphic','Social'].map(t => `<option value="${t}">${t}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Dimensions</label>
                        <input id="new-dim" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Duration</label>
                        <input id="new-dur" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Objective</label>
                        <textarea id="new-obj" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="2"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Description</label>
                        <textarea id="new-desc" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="3"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="new-notes" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button onclick="closeNewTaskModal()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">Cancel</button>
                    <button onclick="createTask()" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Create</button>
                </div>
            </div>
        </div>
        ` : ''}

        ${editItem ? `
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Edit Task #${editItem.id}</h2>
                    <button onclick="closeEditModal()" class="text-gray-600 hover:text-gray-900" aria-label="Close edit">✕</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Phase</label>
                        <select id="editm-phase-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                            ${[1,2,3,4].map(p => `<option value="${p}" ${p===editItem.phase? 'selected': ''}>${p}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Day</label>
                        <input type="number" id="editm-day-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${editItem.day || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Type</label>
                        <input id="editm-type-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${editItem.content_type || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Team</label>
                        <select id="editm-team-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm">
                            ${['Video','Graphic','Social'].map(t => `<option value="${t}" ${t===editItem.team? 'selected': ''}>${t}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Dimensions</label>
                        <input id="editm-dim-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${editItem.dimensions || ''}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Duration</label>
                        <input id="editm-dur-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" value="${editItem.duration || ''}">
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Objective</label>
                        <textarea id="editm-obj-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="2">${editItem.objective || ''}</textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Description</label>
                        <textarea id="editm-desc-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="3">${editItem.description || ''}</textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="editm-notes-${editItem.id}" class="w-full mt-1 rounded-lg border border-gray-300 p-2 text-sm" rows="2">${editItem.notes || ''}</textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button onclick="closeEditModal()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300">Cancel</button>
                    <button onclick="saveEditModalTask()" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Save</button>
                </div>
            </div>
        </div>
        ` : ''}

        <!-- Phase Goals -->
        <div class="grid grid-cols-2 gap-4 mt-6">
            ${Object.entries(phaseInfo).map(([key, info]) => `
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-3 h-3 rounded-full ${info.color}"></div>
                        <h3 class="font-bold text-lg">Phase ${key}: ${info.name}</h3>
                    </div>
                    <p class="text-sm text-gray-600">${info.goal}</p>
                </div>
            `).join('')}
        </div>
    `;

    document.getElementById('app').innerHTML = appHTML;
}

// Phase filter function
function setPhase(phase) {
    activePhase = phase;
    renderApp();
}

// Start the app when page loads
init();
