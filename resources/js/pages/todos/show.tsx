import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type FamilyMember = { id: number; name: string; username: string };

type Todo = {
    id: number;
    title: string;
    notes?: string;
    type: 'chore' | 'reminder';
    completed: boolean;
    completed_at?: string;
    created_by: number;
    assigned_to: number;
    createdBy: FamilyMember;
    assignedTo: FamilyMember;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'To-Dos', href: '/todos' },
    { title: 'To-Do', href: '#' },
];

export default function TodoShow({ todo }: { todo: Todo }) {
    const { auth } = usePage().props;
    const [editing, setEditing] = useState(false);

    const isParent = auth.user.status === 'parent';
    const isCreator = auth.user.id === todo.created_by;
    const isAssigned = auth.user.id === todo.assigned_to;
    const canFullEdit = isParent || isCreator;
    const canDelete = isCreator || isParent;

    function handleToggle() {
        router.patch(`/todos/${todo.id}/complete`, {}, { preserveScroll: true });
    }

    function handleDelete() {
        if (!confirm('Delete this to-do?')) return;
        router.delete(`/todos/${todo.id}`, {
            onSuccess: () => router.visit('/todos'),
        });
    }

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.currentTarget));
        router.patch(`/todos/${todo.id}`, data, {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    }

    return (
        <>
            <Head title={todo.title} />
            <div className="p-4 max-w-2xl space-y-6">

                {/* Header */}
                <div className="flex items-start justify-between">
                    <div className="flex items-start gap-3">
                        <input
                            type="checkbox"
                            checked={todo.completed}
                            onChange={handleToggle}
                            disabled={!isAssigned && !canFullEdit}
                            className="mt-1.5 h-4 w-4 cursor-pointer"
                        />
                        <div>
                            <h2 className={`text-2xl font-semibold ${todo.completed ? 'line-through text-muted-foreground' : ''}`}>
                                {todo.title}
                            </h2>
                            <span className="text-xs px-1.5 py-0.5 rounded bg-muted text-muted-foreground capitalize">
                                {todo.type}
                            </span>
                        </div>
                    </div>
                    {!editing && (
                        <div className="flex gap-2">
                            {(canFullEdit || isAssigned) && (
                                <button
                                    onClick={() => setEditing(true)}
                                    className="px-3 py-1.5 border rounded text-sm hover:bg-muted"
                                >
                                    Edit
                                </button>
                            )}
                            {canDelete && (
                                <button
                                    onClick={handleDelete}
                                    className="px-3 py-1.5 text-sm text-destructive border border-destructive rounded hover:bg-destructive/10"
                                >
                                    Delete
                                </button>
                            )}
                        </div>
                    )}
                </div>

                {/* Detail view */}
                {!editing && (
                    <div className="rounded-xl border p-4 space-y-3 text-sm">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide">Assigned to</p>
                                <p>{todo.assignedTo.name} (@{todo.assignedTo.username})</p>
                            </div>
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide">Created by</p>
                                <p>{todo.createdBy.name} (@{todo.createdBy.username})</p>
                            </div>
                            {todo.completed_at && (
                                <div className="col-span-2">
                                    <p className="text-muted-foreground text-xs uppercase tracking-wide">Completed at</p>
                                    <p>{todo.completed_at}</p>
                                </div>
                            )}
                        </div>
                        {todo.notes && (
                            <div>
                                <p className="text-muted-foreground text-xs uppercase tracking-wide mb-1">Notes</p>
                                <p>{todo.notes}</p>
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
                        <h3 className="font-semibold">Edit To-Do</h3>
                        <div className="space-y-3">
                            {canFullEdit && (
                                <div className="grid gap-1">
                                    <label className="text-sm">Title</label>
                                    <input name="title" defaultValue={todo.title} required className="border rounded px-2 py-1 text-sm bg-background" />
                                </div>
                            )}
                            {canFullEdit && (
                                <div className="grid gap-1">
                                    <label className="text-sm">Type</label>
                                    <select name="type" defaultValue={todo.type} className="border rounded px-2 py-1 text-sm bg-background">
                                        <option value="reminder">Reminder</option>
                                        <option value="chore">Chore</option>
                                    </select>
                                </div>
                            )}
                            <div className="grid gap-1">
                                <label className="text-sm">Notes</label>
                                <textarea name="notes" defaultValue={todo.notes} rows={3} className="border rounded px-2 py-1 text-sm bg-background resize-none" />
                            </div>
                            <div className="grid gap-1">
                                <label className="text-sm">Completed</label>
                                <input type="checkbox" name="completed" defaultChecked={todo.completed} className="h-4 w-4" />
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

TodoShow.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={breadcrumbs}>{page}</AppLayout>
);