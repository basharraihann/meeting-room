import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import listPlugin from '@fullcalendar/list'
import interactionPlugin from '@fullcalendar/interaction'
import idLocale from '@fullcalendar/core/locales/id'

import flatpickr from 'flatpickr'
import { Indonesian } from 'flatpickr/dist/l10n/id.js'
import 'flatpickr/dist/flatpickr.css'

document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('calendar')
  if (!el) return

  const userRole = (document.documentElement.getAttribute('data-user-role') || '').toUpperCase()
  const isPIC = userRole === 'PIC'
  const isTU = userRole === 'TU' || userRole === 'ADMIN'

  const roomDotColor = {
    1: '#3d3d3d',
    2: '#5bc8af',
    3: '#7b68aa',
    4: '#f0c040',
    5: '#4bbfd4',
    6: '#e8604c',
    7: '#ec4899',
  }

  let selectedRoomId = new URLSearchParams(window.location.search).get('room_id') || ''

  const sidebar = document.getElementById('room-sidebar')
  const activeRoomLabel = document.getElementById('active-room-label')

  const setActiveSidebar = () => {
    if (!sidebar) return
    sidebar.querySelectorAll('.room-filter').forEach((btn) => {
      const id = btn.dataset.roomId || ''
      btn.classList.toggle('bg-indigo-50', id === String(selectedRoomId))
      btn.classList.toggle('text-indigo-700', id === String(selectedRoomId))
      btn.classList.toggle('font-semibold', id === String(selectedRoomId))
    })
  }

  const setLabel = (name) => {
    if (activeRoomLabel) activeRoomLabel.textContent = name || 'Semua Ruang'
  }

  const pad = (n) => String(n).padStart(2, '0')

  const fmtInputForDatetimeLocal = (d) =>
    `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`

  const fmtHuman = (d) =>
    d
      ? new Date(d).toLocaleString('id-ID', {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        })
      : '-'

  const openDetailModal = (payload) => {
    const modalEl = document.querySelector('[x-data="meetingDetailModal()"]')
    if (!modalEl || !window.Alpine) return
    const modal = window.Alpine.$data(modalEl)
    modal.show(payload)
  }

  const startInput = document.querySelector('input[name="start_at"]')
  const endInput = document.querySelector('input[name="end_at"]')

  let fpStart = null
  let fpEnd = null

  if (isPIC) {
    if (startInput) {
      startInput.type = 'text'
      fpStart = flatpickr(startInput, {
        enableTime: true,
        time_24hr: true,
        locale: Indonesian,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd F Y H:i',
        allowInput: true
      })
    }

    if (endInput) {
      endInput.type = 'text'
      fpEnd = flatpickr(endInput, {
        enableTime: true,
        time_24hr: true,
        locale: Indonesian,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd F Y H:i',
        allowInput: true
      })
    }

    if (fpStart && fpEnd) {
      fpStart.config.onChange.push(function (selectedDates) {
        const s = selectedDates?.[0]
        if (!s) return
        const e = fpEnd.selectedDates?.[0]
        if (!e || e.getTime() <= s.getTime()) {
          fpEnd.setDate(new Date(s.getTime() + 60 * 60 * 1000), true)
        }
      })
    }
  }

  const dotSpan = (color) =>
    `<span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:${color};margin-top:3px;display:inline-block;"></span>`

  const calendarConfig = {
    plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],

    locale: idLocale,
    buttonText: {
      today: 'Hari ini',
      month: 'Bulan',
      week: 'Minggu',
      day: 'Hari',
      list: 'Agenda'
    },

    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

    initialView: window.innerWidth < 640 ? 'listMonth' : 'dayGridMonth',

    headerToolbar: window.innerWidth < 640
      ? { left: 'prev,next', center: 'title', right: 'listMonth,dayGridMonth' }
      : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },

    events: {
      url: '/api/bookings',
      extraParams: () => (selectedRoomId ? { room_id: selectedRoomId } : {}),
      failure() {
        alert('Gagal load booking')
      }
    },

    eventContent(arg) {
      const p = arg.event.extendedProps || {}
      const roomId = p.room_id
      const dot = roomDotColor[roomId] || '#9ca3af'
      const status = p.status || 'PENDING'

      const fmtTime = (d) => {
        if (!d) return ''
        const dt = new Date(d)
        const hh = String(dt.getHours()).padStart(2, '0')
        const mm = String(dt.getMinutes()).padStart(2, '0')
        return `${hh}.${mm}`
      }

      const start = fmtTime(arg.event.start)
      const end = fmtTime(arg.event.end)
      const range = start && end ? `${start} – ${end}` : (arg.timeText || '')
      const title = arg.event.title
      const pic = p.pic ?? '-'

      if (isPIC) {
        return {
          html: `
            <div class="px-2 py-1 rounded-lg bg-white/95 border border-gray-200 shadow-sm overflow-hidden">
              <div class="flex items-start gap-2">
                ${dotSpan(dot)}
                <div class="min-w-0 w-full">
                  <div class="text-[11px] font-semibold text-gray-700 whitespace-nowrap">${range}</div>
                  <div class="text-[12px] font-semibold leading-tight whitespace-nowrap overflow-hidden text-ellipsis">${title}</div>
                  <div class="text-[11px] font-semibold text-gray-700 whitespace-nowrap overflow-hidden text-ellipsis">PIC: ${pic}</div>
                </div>
              </div>
            </div>
          `
        }
      }

      if (isTU) {
        const statusBadge = getStatusBadge(status)
        return {
          html: `
            <div class="px-2 py-1 rounded-lg bg-white/95 border border-gray-200 shadow-sm overflow-hidden cursor-pointer hover:shadow-md transition-shadow">
              <div class="flex items-start gap-2">
                ${dotSpan(dot)}
                <div class="min-w-0 flex-1">
                  <div class="text-[11px] font-semibold text-gray-700 whitespace-nowrap">${range}</div>
                  <div class="text-[12px] font-semibold leading-tight whitespace-nowrap overflow-hidden text-ellipsis">${title}</div>
                  <div class="flex items-center gap-1 mt-0.5">
                    <span class="text-[10px] px-1.5 py-0.5 rounded ${statusBadge}">${status}</span>
                  </div>
                </div>
              </div>
            </div>
          `
        }
      }

      return {
        html: `
          <div class="px-2 py-1 rounded-lg bg-white/95 border border-gray-200 shadow-sm">
            <span class="text-[11px] font-semibold">${title}</span>
          </div>
        `
      }
    },

    dateClick: isPIC ? (info) => {
      const startStr =
        info.dateStr.length === 10 ? info.dateStr + 'T09:00' : info.dateStr.slice(0, 16)
      const startDate = new Date(startStr)
      const endDate = new Date(startDate.getTime() + 60 * 60 * 1000)

      window.dispatchEvent(
        new CustomEvent('open-booking-modal', {
          detail: {
            start: fmtInputForDatetimeLocal(startDate),
            end: fmtInputForDatetimeLocal(endDate)
          }
        })
      )

      if (fpStart) fpStart.setDate(startDate, true)
      if (fpEnd) fpEnd.setDate(endDate, true)
    } : undefined,

    eventClick(info) {
      info.jsEvent.preventDefault()
      const p = info.event.extendedProps || {}
      openDetailModal({
        title: info.event.title,
        room: p.room_name ? `Ruang: ${p.room_name}` : '',
        status: p.status || '',
        pic: p.pic ?? '',
        start: fmtHuman(info.event.start),
        end: fmtHuman(info.event.end),
        description: p.description ?? '',
        role: userRole
      })
    }
  }

  if (isPIC) {
    calendarConfig.selectable = true
    calendarConfig.selectConstraint = 'businessHours'
  }

  const calendar = new Calendar(el, calendarConfig)
  calendar.render()

  if (isPIC) {
    setActiveSidebar()

    if (sidebar) {
      const initBtn = sidebar.querySelector(`.room-filter[data-room-id="${selectedRoomId}"]`)
      setLabel(initBtn?.dataset.roomName || 'Semua Ruang')

      sidebar.addEventListener('click', (e) => {
        const btn = e.target.closest('.room-filter')
        if (!btn) return

        selectedRoomId = btn.dataset.roomId || ''
        const roomName = btn.dataset.roomName || 'Semua Ruang'

        setLabel(roomName)
        setActiveSidebar()

        const url = new URL(window.location.href)
        if (selectedRoomId) url.searchParams.set('room_id', selectedRoomId)
        else url.searchParams.delete('room_id')

        window.history.replaceState({}, '', url.toString())
        calendar.refetchEvents()
      })
    }
  }
})

function getStatusBadge(status) {
  const s = (status || '').toUpperCase()
  if (s === 'APPROVED') return 'bg-green-100 text-green-700 font-semibold text-xs'
  if (s === 'PENDING') return 'bg-yellow-100 text-yellow-700 font-semibold text-xs'
  if (s === 'REJECTED') return 'bg-red-100 text-red-700 font-semibold text-xs'
  return 'bg-gray-100 text-gray-700 font-semibold text-xs'
}