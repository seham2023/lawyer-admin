<?php

namespace App\Filament\Widgets;

use App\Models\CaseRecord;
use App\Models\Session;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Data\EventData;
use Illuminate\Database\Eloquent\Model;

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
                ->start($case->start_date)
                ->backgroundColor('#10b981') // Green for cases
                ->borderColor('#059669')
                ->textColor('#ffffff')
                ->url(route('filament.admin.resources.case-records.edit', $case->id))
                ->extendedProps([
                    'type' => 'case',
                    'court' => $case->court_name,
                    'subject' => $case->subject,
                    'client' => $case->client->name ?? 'Unknown',
                    'category' => $case->category->name ?? 'No Category',
                    'status' => $case->status->name ?? 'No Status',
                ]);
        }

        // Add sessions to events
        foreach ($sessions as $session) {
            $events[] = EventData::make()
                ->id('session-' . $session->id)
                ->title('Session: ' . $session->title)
                ->start($session->datetime)
                ->backgroundColor('#3b82f6') // Blue for sessions
                ->borderColor('#1d4ed8')
                ->textColor('#ffffff')
                ->extendedProps([
                    'type' => 'session',
                    'case_number' => $session->case_number,
                    'details' => $session->details,
                    'priority' => $session->priority,
                ]);
        }

        return $events;
    }

    public function getViewData(): array
    {
        return [
            'plugins' => ['dayGridPlugin', 'timeGridPlugin', 'interactionPlugin'],
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay'
            ],
            'initialView' => 'dayGridMonth',
            'navLinks' => true,
            'editable' => false,
            'dayMaxEvents' => true,
            'selectable' => false,
            'height' => 'auto',
            'eventClick' => [
                'callback' => 'function(info) {
                    const event = info.event;
                    const props = event.extendedProps;
                    
                    let content = "";
                    if (props.type === "case") {
                        content = `
                            <div class="space-y-2">
                                <h3 class="font-semibold text-lg">${event.title}</h3>
                                <p><strong>Court:</strong> ${props.court}</p>
                                <p><strong>Subject:</strong> ${props.subject}</p>
                                <p><strong>Category:</strong> ${props.category}</p>
                                <p><strong>Status:</strong> ${props.status}</p>
                                <p><strong>Date:</strong> ${event.start.toLocaleDateString()}</p>
                            </div>
                        `;
                    } else if (props.type === "session") {
                        content = `
                            <div class="space-y-2">
                                <h3 class="font-semibold text-lg">${event.title}</h3>
                                <p><strong>Case Number:</strong> ${props.case_number}</p>
                                <p><strong>Details:</strong> ${props.details}</p>
                                <p><strong>Priority:</strong> ${props.priority}</p>
                                <p><strong>Date:</strong> ${event.start.toLocaleDateString()} ${event.start.toLocaleTimeString()}</p>
                            </div>
                        `;
                    }
                    
                    // Create a modal or tooltip to show event details
                    alert(event.title + "\\n" + (props.court || props.case_number));
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
                
                // Add tooltip or additional styling based on event type
                if (props.type === "case") {
                    info.el.setAttribute("title", "Case: " + props.subject + " - " + props.court);
                } else if (props.type === "session") {
                    info.el.setAttribute("title", "Session: " + props.details);
                }
            }
        ';
    }
}
