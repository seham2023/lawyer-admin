<?php

namespace App\Filament\Widgets;

use App\Models\CaseRecord;
use App\Models\Session;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class CalendarWidget extends FullCalendarWidget
{
    protected static ?string $heading = 'Legal Calendar';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;

    public function fetchEvents(array $fetchInfo): array
    {
        // Fetch case records with start dates
        $caseRecords = CaseRecord::query()
            ->with(['client', 'category', 'status'])
            ->whereBetween('start_date', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();

        // Fetch sessions with datetime
        $sessions = Session::query()
            ->whereBetween('datetime', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();

        $events = [];

        // Add case records to events
        foreach ($caseRecords as $case) {
            $events[] = EventData::make()
                ->id('case-' . $case->id)
                ->title('Case: ' . ($case->client->name ?? 'Unknown Client'))
                ->start($case->start_date->format('Y-m-d'))
                ->backgroundColor('#10b981')
                ->borderColor('#059669')
                ->textColor('#ffffff')
                ->url(route('filament.admin.resources.cases.edit', $case->id))
                ->extendedProps([
                    'type' => 'case',
                    'court' => $case->court_name,
                    'subject' => $case->subject,
                    'client' => $case->client->name ?? 'Unknown',
                    'category' => $case->category->name ?? 'No Category',
                    'status' => $case->status->name ?? 'No Status',
                ]);
        }

        // Add sessions to events - Fixed datetime format
        foreach ($sessions as $session) {
            $events[] = EventData::make()
                ->id('session-' . $session->id)
                ->title('Session: ' . $session->title)
                ->start($session->datetime->format('Y-m-d\TH:i:s'))
                ->backgroundColor('#3b82f6')
                ->borderColor('#1d4ed8')
                ->textColor('#ffffff')
                ->extendedProps([
                    'type' => 'session',
                    'case_number' => $session->case_number,
                    'details' => $session->details,
                    'priority' => $session->priority,
                ]);
        }

        // Debug logging
        \Log::info('Calendar Events Generated', [
            'case_count' => count($caseRecords),
            'session_count' => count($sessions),
            'total_events' => count($events),
            'events_sample' => array_slice($events, 0, 2)
        ]);

        return $events;
    }

    public function getViewData(): array
    {
        return [
            'plugins' => ['dayGridPlugin', 'timeGridPlugin', 'interactionPlugin'],
            'timeZone' => 'local',
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay'
            ],
            'height' => 'auto',
            'navLinks' => true,
            'editable' => false,
            'dayMaxEvents' => true,
            'selectable' => false,
            'displayEventTime' => true,
            'eventTimeFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'omitZeroMinute' => false,
                'meridiem' => 'short'
            ],
            'slotLabelFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'omitZeroMinute' => false,
                'meridiem' => 'short'
            ],
            'eventClick' => [
                'callback' => 'function(info) {
                    const event = info.event;
                    const props = event.extendedProps;
                    
                    let content = "";
                    if (props.type === "case") {
                        content = `
                            <div class="space-y-2 p-4">
                                <h3 class="font-semibold text-lg text-gray-900">${event.title}</h3>
                                <div class="grid grid-cols-1 gap-2">
                                    <p><span class="font-medium">Court:</span> ${props.court || "N/A"}</p>
                                    <p><span class="font-medium">Subject:</span> ${props.subject || "N/A"}</p>
                                    <p><span class="font-medium">Category:</span> ${props.category}</p>
                                    <p><span class="font-medium">Status:</span> ${props.status}</p>
                                    <p><span class="font-medium">Date:</span> ${event.start.toLocaleDateString()}</p>
                                </div>
                            </div>
                        `;
                    } else if (props.type === "session") {
                        content = `
                            <div class="space-y-2 p-4">
                                <h3 class="font-semibold text-lg text-gray-900">${event.title}</h3>
                                <div class="grid grid-cols-1 gap-2">
                                    <p><span class="font-medium">Case Number:</span> ${props.case_number || "N/A"}</p>
                                    <p><span class="font-medium">Details:</span> ${props.details || "No details"}</p>
                                    <p><span class="font-medium">Priority:</span> ${props.priority || "Normal"}</p>
                                    <p><span class="font-medium">Date & Time:</span> ${event.start.toLocaleDateString()} ${event.start.toLocaleTimeString()}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Enhanced modal display
                    const modal = document.createElement("div");
                    modal.className = "fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50";
                    modal.innerHTML = `
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                            ${content}
                            <div class="px-4 pb-4">
                                <button onclick="this.closest(\".fixed\").remove()" 
                                        class="w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
                                    Close
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                    
                    // Close on outside click
                    modal.addEventListener("click", function(e) {
                        if (e.target === modal) {
                            modal.remove();
                        }
                    });
                }'
            ]
        ];
    }

    public function eventDidMount(): string
    {
        return '
            function(info) {
                const event = info.event;
                const props = event.extendedProps;
                
                // Add enhanced tooltips
                if (props.type === "case") {
                    info.el.setAttribute("title", 
                        "Case: " + props.subject + " - Court: " + props.court + 
                        " - Status: " + props.status
                    );
                } else if (props.type === "session") {
                    info.el.setAttribute("title", 
                        "Session: " + props.details + " - Priority: " + props.priority +
                        " - Case: " + props.case_number
                    );
                }
                
                // Add custom CSS classes for styling
                if (props.priority === "high") {
                    info.el.style.border = "2px solid #dc2626";
                } else if (props.priority === "low") {
                    info.el.style.border = "2px solid #16a34a";
                }
            }
        ';
    }

    // Optional: Add refresh functionality
    protected function getListeners(): array
    {
        return [
            'refreshCalendar' => 'refreshEvents',
        ];
    }

    public function refreshEvents(): void
    {
        $this->dispatch('refresh-calendar');
    }
}
