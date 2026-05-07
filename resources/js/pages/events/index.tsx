import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type Event = {
    id: number;
    name: string;
    location: string;
    start_date: string;
    end_date: string;
    start_time: string;
    end_time: string;
    description?: string;
    user: { id: number; name: string; username: string };
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Events', href: '/events' }];

export default function Events({ events }: { events: Event[] }) {
    const { auth } = usePage().props;
    const [showForm, setShowForm] = useState(false);

    function handleDelete(id: number) {
        if (!confirm('Delete this event?')) return;
        router.delete(`/events/${id}`, { preserveScroll: true });
    }

    return (
        <>
            <Head title="Events" />
            <div className="p-4 space-y-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold">Family Events</h2>
                    <button
                        onClick={() => setShowForm(true)}
                        className="px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm"
                    >
                        + New Event
                    </button>
                </div>

                {showForm && (
                    <EventForm
                        onClose={() => setShowForm(false)}
                    />
                )}

                <div className="space-y-3">
                    {events.length === 0 && (
                        <p className="text-muted-foreground text-sm">No events yet.</p>
                    )}
                    {events.map(event => (
                        <EventCard
                            key={event.id}
                            event={event}
                            canEdit={
                                auth.user.id === event.user.id ||
                                auth.user.status === 'parent'
                            }
                            onDelete={() => handleDelete(event.id)}
                        />
                    ))}
                </div>
            </div>
        </>
    );
}

function EventCard({
    event,
    canEdit,
    onDelete,
}: {
    event: Event;
    canEdit: boolean;
    onDelete: () => void;
}) {
    const [editing, setEditing] = useState(false);

    if (editing) {
        return <EventForm event={event} onClose={() => setEditing(false)} />;
    }

    return (
        <div className="rounded-xl border border-border p-4 space-y-1">
            <div className="flex items-start justify-between">
                <div>
                    <p className="font-medium">{event.name}</p>
                    <p className="text-sm text-muted-foreground">{event.location}</p>
                </div>
                {canEdit && (
                    <div className="flex gap-2">
                        <button
                            onClick={() => setEditing(true)}
                            className="text-xs text-muted-foreground hover:text-foreground"
                        >
                            Edit
                        </button>
                        <button
                            onClick={onDelete}
                            className="text-xs text-destructive hover:underline"
                        >
                            Delete
                        </button>
                    </div>
                )}
            </div>
            <p className="text-xs text-muted-foreground">
                {event.start_date} {event.start_time} → {event.end_date} {event.end_time}
            </p>
            {event.description && (
                <p className="text-sm">{event.description}</p>
            )}
            <p className="text-xs text-muted-foreground">
                By {event.user.name} (@{event.user.username})
            </p>
        </div>
    );
}

function EventForm({
    event,
    onClose,
}: {
    event?: Event;
    onClose: () => void;
}) {
    const isEdit = !!event;

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.currentTarget));
        if (isEdit) {
            router.patch(`/events/${event.id}`, data, {
                preserveScroll: true,
                onSuccess: onClose,
            });
        } else {
            router.post('/events', data, {
                preserveScroll: true,
                onSuccess: onClose,
            });
        }
    }

    return (
        <form
            onSubmit={handleSubmit}
            className="rounded-xl border border-border p-4 space-y-3 bg-card"
        >
            <h3 className="font-semibold">{isEdit ? 'Edit Event' : 'New Event'}</h3>
            <div className="grid grid-cols-2 gap-3">
                <div className="col-span-2 grid gap-1">
                    <label className="text-sm">Name</label>
                    <input name="name" defaultValue={event?.name} required className="border rounded px-2 py-1 text-sm bg-background" />
                </div>
                <div className="col-span-2 grid gap-1">
                    <label className="text-sm">Location</label>
                    <input name="location" defaultValue={event?.location} required className="border rounded px-2 py-1 text-sm bg-background" />
                </div>
                <div className="grid gap-1">
                    <label className="text-sm">Start date</label>
                    <input name="start_date" type="date" defaultValue={event?.start_date} required className="border rounded px-2 py-1 text-sm bg-background" />
                </div>
                <div className="grid gap-1">
                    <label className="text-sm">End date</label>
                    <input name="end_date" type="date" defaultValue={event?.end_date} required className="border rounded px-2 py-1 text-sm bg-background" />
                </div>
                <div className="grid gap-1">
                    <label className="text-sm">Start time</label>
                    <input name="start_time" type="time" defaultValue={event?.start_time} required className="border rounded px-2 py-1 text-sm bg-background" />
                </div>
                <div className="grid gap-1">
                    <label className="text-sm">End time</label>
                    <input name="end_time" type="time" defaultValue={event?.end_time} required className="border rounded px-2 py-1 text-sm bg-background" />
                </div>
                <div className="col-span-2 grid gap-1">
                    <label className="text-sm">Description</label>
                    <textarea name="description" defaultValue={event?.description} rows={2} className="border rounded px-2 py-1 text-sm bg-background resize-none" />
                </div>
            </div>
            <div className="flex gap-2 justify-end">
                <button type="button" onClick={onClose} className="text-sm text-muted-foreground">Cancel</button>
                <button type="submit" className="px-3 py-1 bg-primary text-primary-foreground rounded text-sm">
                    {isEdit ? 'Save' : 'Create'}
                </button>
            </div>
        </form>
    );
}

Events.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={breadcrumbs}>{page}</AppLayout>
);