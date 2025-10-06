<?php

namespace App\Filament\Widgets;

use App\Models\Session;
use App\Models\Event;
use App\Models\CaseRecord;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;
use Filament\Forms;
use Filament\Actions\Action;
use Carbon\Carbon;

class CalendarWidget extends FullCalendarWidget
{
    // Handle both Event and Session models
    public Model | string | null $model = Event::class;
    
    protected static ?string $heading = 'Legal Calendar';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;

    public function fetchEvents(array $fetchInfo): array
    {
        // Fetch sessions
        $sessions = Session::query()
            ->whereBetween('datetime', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();

        // Fetch events 
        $events = Event::query()
            ->where(function ($query) use ($fetchInfo) {
                $query->whereBetween('start', [$fetchInfo['start'], $fetchInfo['end']])
                      ->orWhereBetween('end', [$fetchInfo['start'], $fetchInfo['end']]);
            })
            ->get();

        $calendarEvents = [];

        // Add sessions to calendar
        foreach ($sessions as $session) {
            $sessionDateTime = is_string($session->datetime)
                ? Carbon::parse($session->datetime)
                : $session->datetime;

            $calendarEvents[] = [
                'id' => $session->id, // Use direct ID for proper resolution
                'title' => 'Session: ' . $session->title,
                'start' => $sessionDateTime->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $this->getSessionColor($session->priority),
                'borderColor' => $this->getSessionBorderColor($session->priority),
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'session',
                    'case_number' => $session->case_number,
                    'details' => $session->details,
                    'priority' => $session->priority,
                    'model_id' => $session->id,
                ]
            ];
        }

        // Add general events to calendar  
        foreach ($events as $event) {
            $startDateTime = is_string($event->start)
                ? Carbon::parse($event->start)
                : $event->start;

            $endDateTime = $event->end 
                ? (is_string($event->end) ? Carbon::parse($event->end) : $event->end)
                : null;

            $calendarEvents[] = [
                'id' => $event->id, // Use direct ID for actionable events
                'title' => $event->title,
                'start' => $event->all_day 
                    ? $startDateTime->format('Y-m-d')
                    : $startDateTime->format('Y-m-d\TH:i:s'),
                'end' => $endDateTime 
                    ? ($event->all_day 
                        ? $endDateTime->format('Y-m-d') 
                        : $endDateTime->format('Y-m-d\TH:i:s'))
                    : null,
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'textColor' => '#ffffff',
                'allDay' => $event->all_day,
                'extendedProps' => [
                    'type' => 'event',
                    'description' => $event->description,
                    'event_type' => $event->type,
                ]
            ];
        }

        return $calendarEvents;
    }

    // Separate form schemas for events and sessions
    public function getEventFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('title')
                ->required()
                ->label('Event Title'),

            Forms\Components\Textarea::make('description')
                ->label('Event Description')
                ->rows(3),

            Forms\Components\DateTimePicker::make('start')
                ->required()
                ->label('Start Date & Time'),

            Forms\Components\DateTimePicker::make('end')
                ->label('End Date & Time')
                ->nullable(),

            Forms\Components\Select::make('type')
                ->options([
                    'general' => 'General',
                    'meeting' => 'Meeting',
                    'holiday' => 'Holiday',
                    'deadline' => 'Deadline',
                    'appointment' => 'Appointment',
                ])
                ->default('general'),

            Forms\Components\ColorPicker::make('color')
                ->label('Event Color')
                ->default('#3b82f6'),

            Forms\Components\Toggle::make('all_day')
                ->label('All Day Event')
                ->default(false),
        ];
    }

    public function getSessionFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('title')
                ->required()
                ->label('Session Title'),

            Forms\Components\Textarea::make('details')
                ->label('Session Details')
                ->rows(3),

            Forms\Components\DateTimePicker::make('datetime')
                ->required()
                ->label('Session Date & Time'),

            Forms\Components\Select::make('case_record_id')
                ->label('Case')
                ->options(function () {
                    return CaseRecord::with('client')
                        ->get()
                        ->mapWithKeys(function ($case) {
                            $clientName = $case->client?->name ?? 'Unknown Client';
                            return [$case->id => "Case #{$case->id} - {$clientName}"];
                        });
                })
                ->required()
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('priority')
                ->options([
                    'low' => 'Low',
                    'normal' => 'Normal',
                    'high' => 'High',
                ])
                ->default('normal'),

            Forms\Components\Hidden::make('case_number')
                ->default(fn ($get) => CaseRecord::find($get('case_record_id'))?->id),
        ];
    }

    // Header actions for creating events and sessions separately
    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make('add_event')
                ->label('Add Event')
                ->form($this->getEventFormSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    return $data;
                })
                ->mountUsing(function (Forms\Form $form, array $arguments) {
                    $form->fill([
                        'start' => $arguments['start'] ?? now(),
                        'end' => $arguments['end'] ?? now()->addHour(),
                    ]);
                })
                ->action(function (array $data) {
                    $this->createEvent($data);
                }),

            Actions\CreateAction::make('add_session')
                ->label('Add Session')
                ->form($this->getSessionFormSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    return $data;
                })
                ->mountUsing(function (Forms\Form $form, array $arguments) {
                    $form->fill([
                        'datetime' => $arguments['start'] ?? now(),
                    ]);
                })
                ->action(function (array $data) {
                    $this->createSession($data);
                }),
        ];
    }

    // Modal actions for events and sessions
    protected function modalActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('View Details'),

            Actions\EditAction::make()
                ->form(function ($record) {
                    // Return the appropriate form schema based on record type
                    if ($record instanceof Session) {
                        return $this->getSessionFormSchema();
                    }
                    return $this->getEventFormSchema();
                })
                ->mountUsing(function ($record, Forms\Form $form, array $arguments) {
                    // Determine if this is a session or event based on the record type
                    if ($record instanceof Session) {
                        $form->fill([
                            'title' => $record->title,
                            'details' => $record->details,
                            'datetime' => $record->datetime,
                            'case_record_id' => $record->case_record_id,
                            'priority' => $record->priority,
                        ]);
                    } else {
                        $eventData = $arguments['event'] ?? [];
                        $form->fill([
                            'title' => $record->title,
                            'description' => $record->description,
                            'type' => $record->type,
                            'color' => $record->color,
                            'all_day' => $record->all_day,
                            'start' => $eventData['start'] ?? $record->start,
                            'end' => $eventData['end'] ?? $record->end,
                        ]);
                    }
                })
                ->action(function ($record, array $data) {
                    // Use the appropriate update method based on record type
                    if ($record instanceof Session) {
                        return $this->updateSession($record, $data);
                    } else {
                        return $this->updateEvent($record, $data);
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make()
            ->form(function ($record) {
                if ($record instanceof Session) {
                    return $this->getSessionViewSchema($record);
                }
                return $this->getEventViewSchema($record);
            })
            ->modalHeading(function ($record) {
                if ($record instanceof Session) {
                    return 'Session Details';
                }
                return 'Event Details';
            });
    }

    // View schemas for display-only
    public function getEventViewSchema($record): array
    {
        return [
            Forms\Components\Section::make('Event Details')
                ->schema([
                    Forms\Components\Placeholder::make('title')
                        ->label('Event Title')
                        ->content($record->title),

                    Forms\Components\Placeholder::make('description')
                        ->label('Description')
                        ->content($record->description ?: 'No description'),

                    Forms\Components\Placeholder::make('start')
                        ->label('Start Date & Time')
                        ->content($record->start?->format('Y-m-d H:i:s') ?: 'Not set'),

                    Forms\Components\Placeholder::make('end')
                        ->label('End Date & Time')
                        ->content($record->end?->format('Y-m-d H:i:s') ?: 'Not set'),

                    Forms\Components\Placeholder::make('type')
                        ->label('Event Type')
                        ->content(ucfirst($record->type ?: 'general')),

                    Forms\Components\Placeholder::make('all_day')
                        ->label('All Day Event')
                        ->content($record->all_day ? 'Yes' : 'No'),
                ])
        ];
    }

    public function getSessionViewSchema($record): array
    {
        $caseRecord = CaseRecord::with('client')->find($record->case_record_id);
        $caseInfo = $caseRecord && $caseRecord->client
            ? "Case #{$caseRecord->id} - {$caseRecord->client->name}"
            : "Case #{$record->case_record_id}";

        return [
            Forms\Components\Section::make('Session Details')
                ->schema([
                    Forms\Components\Placeholder::make('title')
                        ->label('Session Title')
                        ->content($record->title),

                    Forms\Components\Placeholder::make('details')
                        ->label('Details')
                        ->content($record->details ?: 'No details'),

                    Forms\Components\Placeholder::make('datetime')
                        ->label('Date & Time')
                        ->content($record->datetime?->format('Y-m-d H:i:s') ?: 'Not set'),

                    Forms\Components\Placeholder::make('case_record_id')
                        ->label('Case')
                        ->content($caseInfo),

                    Forms\Components\Placeholder::make('priority')
                        ->label('Priority')
                        ->content(function () use ($record) {
                            return match($record->priority) {
                                'high' => '🔴 High',
                                'normal' => '🔵 Normal',
                                'low' => '🟢 Low',
                                default => '🔵 Normal',
                            };
                        }),
                ])
        ];
    }

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay'
            ],
            'height' => 'auto',
            'navLinks' => true,
            'editable' => true,
            'selectable' => true,
            'selectMirror' => true,
            'dayMaxEvents' => true,
            'displayEventTime' => true,
            'eventTimeFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'meridiem' => 'short'
            ],
            // Custom event click - let Filament handle all modals
            'eventClick' => [
                'callback' => 'function(info) {
                    // Let Filament handle the modal for both events and sessions
                    // The viewAction and modalActions will determine the display
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
                
                if (props.type === "session") {
                    info.el.setAttribute("title", 
                        "Session: " + (props.details || "No details") + 
                        " - Priority: " + props.priority
                    );
                    info.el.style.cursor = "help";
                } else if (props.type === "event") {
                    info.el.setAttribute("title", 
                        (props.description || "No description") + 
                        " - Type: " + props.event_type
                    );
                    info.el.style.cursor = "pointer";
                }
                
                // Priority styling for sessions
                if (props.priority === "high") {
                    info.el.style.border = "2px solid #dc2626";
                    info.el.style.fontWeight = "bold";
                } else if (props.priority === "low") {
                    info.el.style.border = "2px solid #10b981";
                }
            }
        ';
    }

    // Resolve both Event and Session models for actions
    public function resolveRecord($key): Model
    {
        // Handle session IDs (prefixed with 'session-')
        if (is_string($key) && str_starts_with($key, 'session-')) {
            $sessionId = str_replace('session-', '', $key);
            return Session::findOrFail($sessionId);
        }

        // Handle direct event IDs
        if (is_numeric($key)) {
            $event = Event::find($key);
            if ($event) {
                return $event;
            }

            // If not found as event, try as session
            $session = Session::find($key);
            if ($session) {
                return $session;
            }

            // If neither found, throw exception
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Record not found");
        }

        // Return empty event model as fallback
        return new Event();
    }

    public static function canView(): bool
    {
        return true;
    }

    // Handle creation of Events
    public function createEvent(array $data): Model
    {
        return Event::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start' => $data['start'],
            'end' => $data['end'] ?? null,
            'type' => $data['type'] ?? 'general',
            'color' => $data['color'] ?? '#3b82f6',
            'all_day' => $data['all_day'] ?? false,
        ]);
    }

    // Handle creation of Sessions
    public function createSession(array $data): Model
    {
        return Session::create([
            'title' => $data['title'],
            'details' => $data['details'] ?? null,
            'datetime' => $data['datetime'],
            'case_record_id' => $data['case_record_id'],
            'priority' => $data['priority'] ?? 'normal',
            'case_number' => $data['case_record_id'], // Use case_record_id as case_number
        ]);
    }

    // Handle updates of Events
    public function updateEvent($record, array $data): Model
    {
        $record->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start' => $data['start'],
            'end' => $data['end'] ?? null,
            'type' => $data['type'] ?? 'general',
            'color' => $data['color'] ?? '#3b82f6',
            'all_day' => $data['all_day'] ?? false,
        ]);

        return $record;
    }

    // Handle updates of Sessions
    public function updateSession($record, array $data): Model
    {
        $record->update([
            'title' => $data['title'],
            'details' => $data['details'] ?? null,
            'datetime' => $data['datetime'],
            'case_record_id' => $data['case_record_id'],
            'priority' => $data['priority'] ?? 'normal',
            'case_number' => $data['case_record_id'],
        ]);

        return $record;
    }

    // Helper method to get session color based on priority
    private function getSessionColor(string $priority): string
    {
        return match($priority) {
            'high' => '#dc2626',     // Red for high priority
            'normal' => '#3b82f6',   // Blue for normal priority
            'low' => '#10b981',      // Green for low priority
            default => '#3b82f6',    // Default blue
        };
    }

    // Helper method to get session border color based on priority
    private function getSessionBorderColor(string $priority): string
    {
        return match($priority) {
            'high' => '#b91c1c',     // Dark red for high priority
            'normal' => '#1d4ed8',   // Dark blue for normal priority
            'low' => '#059669',      // Dark green for low priority
            default => '#1d4ed8',    // Default dark blue
        };
    }
}
