import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type EventOwner = { id: number; name: string; username: string };

type Event = {
    id: number;
    name: string;
    location: string;
    start_date: string;
    end_date: string;
    start_time: string;
    end_time: string;
    description?: string;
    user: EventOwner;
    user_id: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Events', href: '/events' },
    { title: 'Event', href: '#' },
];

export default function EventShow({ event }: { event: Event }) {
    const { auth } = usePage().props;
    const [editing, setEditing] = useState(false);

    const canEdit =
        auth.user.id === event.user_id || auth.user.status === 'parent';

    function handleDelete() {
        if (!confirm('Delete this event?')) return;
        router.delete(`/events/${event.id}`, {
            onSuccess: () => router.visit('/events'),
        });
    }

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.currentTarget));
        router.patch(`/events/${event.id}`, data, {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    }

    return (
        <>
            <Head title={event.name} />
            <div className="p-4 max-w-2xl space-y-6">

                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h2 className="text-2xl font-semibold">{event.name}</h2>
                        <p className="text-sm text-muted-foreground">
                            By {event.user.name} (@{event.user.username})
                        </p>
                    </div>
                    {canEdit && !editing && (
                        <div className="flex gap-2">
                            <button
                                onClick={() => setEditing(true)}
                                className="px-3 py-1.5 border rounded text-sm hover:bg-muted"
                            >
                                Edit
                            </button>
                            <button
                                onClick={handleDelete}
                                className="px-3 py-1.5 text-sm text-destructive border border-destructive rounded hover:bg-destructive/10"
                            >
                                Delete
                            </button>
                        </div>
                    )}
                </div>

                {/* Detail view */}
                {!editing && (
                    <div className="rounded-xl border p-4 space-y-3">
                        <div className="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide">Location</p>
                                <p>{event.location}</p>
                            </div>
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide">Dates</p>
                                <p>{event.start_date} → {event.end_date}</p>
                            </div>
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide">Start time</p>
                                <p>{event.start_time}</p>
                            </div>
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide">End time</p>
                                <p>{event.end_time}</p>
                            </div>
                        </div>
                        {event.description && (
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide mb-1">Description</p>
                                <p className="text-sm">{event.description}</p>
                            </div>
                        )}
                    </div>
                )}

                {/* Edit form */}
                {editing && (
                    <form
                        onSubmit={handleSubmit}
                        className="rounded-xl border p-4 space-y-3 bg-card"
                    >
                        <h3 className="font-semibold">Edit Event</h3>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="col-span-2 grid gap-1">
                                <label className="text-sm">Name</label>
                                <input name="name" defaultValue={event.name} required className="border rounded px-2 py-1 text-sm bg-background" />
                            </div>
                            <div className="col-span-2 grid gap-1">
                                <label className="text-sm">Location</label>
                                <input name="location" defaultValue={event.location} required className="border rounded px-2 py-1 text-sm bg-background" />
                            </div>
                            <div className="grid gap-1">
                                <label className="text-sm">Start date</label>
                                <input name="start_date" type="date" defaultValue={event.start_date} required className="border rounded px-2 py-1 text-sm bg-background" />
                            </div>
                            <div className="grid gap-1">
                                <label className="text-sm">End date</label>
                                <input name="end_date" type="date" defaultValue={event.end_date} required className="border rounded px-2 py-1 text-sm bg-background" />
                            </div>
                            <div className="grid gap-1">
                                <label className="text-sm">Start time</label>
                                <input name="start_time" type="time" defaultValue={event.start_time} required className="border rounded px-2 py-1 text-sm bg-background" />
                            </div>
                            <div className="grid gap-1">
                                <label className="text-sm">End time</label>
                                <input name="end_time" type="time" defaultValue={event.end_time} required className="border rounded px-2 py-1 text-sm bg-background" />
                            </div>
                            <div className="col-span-2 grid gap-1">
                                <label className="text-sm">Description</label>
                                <textarea name="description" defaultValue={event.description} rows={3} className="border rounded px-2 py-1 text-sm bg-background resize-none" />
                            </div>
                        </div>
                        <div className="flex gap-2 justify-end">
                            <button type="button" onClick={() => setEditing(false)} className="text-sm text-muted-foreground">
                                Cancel
                            </button>
                            <button type="submit" className="px-3 py-1 bg-primary text-primary-foreground rounded text-sm">
                                Save
                            </button>
                        </div>
                    </form>
                )}
            </div>
        </>
    );
}

EventShow.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={breadcrumbs}>{page}</AppLayout>
);