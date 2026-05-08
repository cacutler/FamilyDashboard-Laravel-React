import {Head, router, usePage} from '@inertiajs/react';
import {useState} from 'react';
import AppLayout from '@/layouts/app-layout';
import type {BreadcrumbItem} from '@/types';
type FamilyMember = {
    id: number;
    name: string;
    username: string;
};
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
const breadcrumbs: BreadcrumbItem[] = [{title: 'To-Dos', href: '/todos'}];
export default function Todos({todos, family}: {
    todos: Todo[];
    family: FamilyMember[];
}) {
    const { auth } = usePage().props;
    const isParent = auth.user.status === 'parent';
    const [showForm, setShowForm] = useState(false);
    function handleToggle(todo: Todo) {
        router.patch(`/todos/${todo.id}/complete`, {}, {preserveScroll: true});
    }
    function handleDelete(id: number) {
        if (!confirm('Delete this to-do?')) return;
        router.delete(`/todos/${id}`, {preserveScroll: true});
    }
    return (
        <>
            <Head title="To-Dos"/>
            <div className="p-4 space-y-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold">To-Dos</h2>
                    {isParent && (
                        <button onClick={() => setShowForm(true)} className="px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm">+ New To-Do</button>
                    )}
                </div>
                {showForm && isParent && (
                    <TodoForm family={family} onClose={() => setShowForm(false)}/>
                )}
                <div className="space-y-3">
                    {todos.length === 0 && (
                        <p className="text-muted-foreground text-sm">No to-dos yet.</p>
                    )}
                    {todos.map(todo => (
                        <TodoCard key={todo.id} todo={todo} currentUserId={auth.user.id} isParent={isParent} family={family} onToggle={() => handleToggle(todo)} onDelete={() => handleDelete(todo.id)}/>
                    ))}
                </div>
            </div>
        </>
    );
}
function TodoCard({todo, currentUserId, isParent, family, onToggle, onDelete}: {
    todo: Todo;
    currentUserId: number;
    isParent: boolean;
    family: FamilyMember[];
    onToggle: () => void;
    onDelete: () => void;
}) {
    const [editing, setEditing] = useState(false);
    const isCreator = currentUserId === todo.created_by;
    const isAssigned = currentUserId === todo.assigned_to;
    const canFullEdit = isParent || isCreator;
    const canDelete = isCreator || isParent;
    if (editing) {
        return (<TodoForm todo={todo} family={family} isParent={isParent} onClose={() => setEditing(false)}/>);
    }
    return (
        <div className={`rounded-xl border p-4 space-y-1 ${todo.completed ? 'opacity-60' : ''}`}>
            <div className="flex items-start gap-3">
                <input type="checkbox"checked={todo.completed} onChange={onToggle}disabled={!isAssigned && !canFullEdit} className="mt-1 h-4 w-4 cursor-pointer"/>
                <div className="flex-1">
                    <div className="flex items-start justify-between">
                        <div>
                            <p className={`font-medium ${todo.completed ? 'line-through text-muted-foreground' : ''}`}>{todo.title}</p>
                            <span className="text-xs px-1.5 py-0.5 rounded bg-muted text-muted-foreground capitalize">{todo.type}</span>
                        </div>
                        <div className="flex gap-2">
                            {(canFullEdit || isAssigned) && (
                                <button onClick={() => setEditing(true)} className="text-xs text-muted-foreground hover:text-foreground">Edit</button>
                            )}
                            {canDelete && (
                                <button onClick={onDelete} className="text-xs text-destructive hover:underline">Delete</button>
                            )}
                        </div>
                    </div>
                    {todo.notes && <p className="text-sm mt-1">{todo.notes}</p>}
                    <p className="text-xs text-muted-foreground mt-1">Assigned to {todo.assignedTo.name} · Created by {todo.createdBy.name}</p>
                </div>
            </div>
        </div>
    );
}
function TodoForm({todo, family, isParent, onClose}: {
    todo?: Todo;
    family: FamilyMember[];
    isParent?: boolean;
    onClose: () => void;
}) {
    const isEdit = !!todo;
    function handleSubmit(e: React.SubmitEvent<HTMLFormElement>) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(e.currentTarget));
        if (isEdit) {
            router.patch(`/todos/${todo.id}`, data, {preserveScroll: true, onSuccess: onClose});
        } else {
            router.post('/todos', data, {preserveScroll: true, onSuccess: onClose});
        }
    }
    return (
        <form onSubmit={handleSubmit} className="rounded-xl border border-border p-4 space-y-3 bg-card">
            <h3 className="font-semibold">{isEdit ? 'Edit To-Do' : 'New To-Do'}</h3>
            <div className="space-y-3">
                {isParent && !isEdit && (
                    <>
                        <div className="grid gap-1">
                            <label className="text-sm">Title</label>
                            <input name="title" defaultValue="" required className="border rounded px-2 py-1 text-sm bg-background" />
                        </div>
                        <div className="grid gap-1">
                            <label className="text-sm">Type</label>
                            <select name="type" defaultValue="reminder" className="border rounded px-2 py-1 text-sm bg-background">
                                <option value="reminder">Reminder</option>
                                <option value="chore">Chore</option>
                            </select>
                        </div>
                        <div className="grid gap-1">
                            <label className="text-sm">Assign to</label>
                            <select name="assigned_to" defaultValue="" required className="border rounded px-2 py-1 text-sm bg-background">
                                {family.map(m => (
                                    <option key={m.id} value={m.id}>{m.name} (@{m.username})</option>
                                ))}
                            </select>
                        </div>
                    </>
                )}
                {isEdit && isParent && (
                    <div className="grid gap-1">
                        <label className="text-sm">Title</label>
                        <input name="title" defaultValue={todo?.title} required className="border rounded px-2 py-1 text-sm bg-background"/>
                    </div>
                )}
                <div className="grid gap-1">
                    <label className="text-sm">Notes</label>
                    <textarea name="notes" defaultValue={todo?.notes} rows={2} className="border rounded px-2 py-1 text-sm bg-background resize-none"/>
                </div>
            </div>
            <div className="flex gap-2 justify-end">
                <button type="button" onClick={onClose} className="text-sm text-muted-foreground">Cancel</button>
                <button type="submit" className="px-3 py-1 bg-primary text-primary-foreground rounded text-sm">{isEdit ? 'Save' : 'Create'}</button>
            </div>
        </form>
    );
}
Todos.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={breadcrumbs}>{page}</AppLayout>
);