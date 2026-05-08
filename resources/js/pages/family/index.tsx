import {Head, router, usePage} from '@inertiajs/react';
import {useState} from 'react';
import AppLayout from '@/layouts/app-layout';
import type {BreadcrumbItem} from '@/types';
type Child = {
    id: number;
    name: string;
    username: string;
    email: string;
    birthdate: string;
    status: 'parent' | 'child';
};
type Parent = {
    id: number;
    name: string;
    username: string;
    email: string;
};
const breadcrumbs: BreadcrumbItem[] = [{title: 'Family', href: '/family'}];
export default function Family({children, parents}: {
    children?: Child[];       // present for parents
    parents?: Parent[];       // present for children
}) {
    const { auth } = usePage().props;
    const isParent = auth.user.status === 'parent';
    const [username, setUsername] = useState('');
    const [error, setError] = useState('');
    function handleLink(e: React.FormEvent) {
        e.preventDefault();
        setError('');
        router.post('/family/link', {username}, {preserveScroll: true, onSuccess: () => setUsername(''), onError: (errs) => setError(errs.username ?? 'Something went wrong.')});
    }
    function handleUnlink(childId: number, name: string) {
        if (!confirm(`Remove ${name} from your family?`)) {
            return
        };
        router.delete(`/family/${childId}`, {preserveScroll: true});
    }
    return (
        <>
            <Head title="Family" />
            <div className="p-4 space-y-6">
                <h2 className="text-xl font-semibold">Family</h2>
                {isParent && (
                    <>
                        <section className="space-y-3">
                            <h3 className="text-base font-medium">Link a child account</h3>
                            <form onSubmit={handleLink} className="flex gap-2 items-start">
                                <div className="flex flex-col gap-1">
                                    <input type="text" placeholder="Child's username" value={username} onChange={e => setUsername(e.target.value)} required className="border rounded px-3 py-1.5 text-sm bg-background"/>
                                    {error && <p className="text-xs text-destructive">{error}</p>}
                                </div>
                                <button type="submit" className="px-4 py-1.5 bg-primary text-primary-foreground rounded text-sm">Link</button>
                            </form>
                        </section>
                        <section className="space-y-3">
                            <h3 className="text-base font-medium">Your children</h3>
                            {children?.length === 0 && (
                                <p className="text-sm text-muted-foreground">No children linked yet.</p>
                            )}
                            {children?.map(child => (
                                <div key={child.id} className="flex items-center justify-between rounded-xl border p-4">
                                    <div>
                                        <p className="font-medium">{child.name}</p>
                                        <p className="text-sm text-muted-foreground">@{child.username} · {child.email}</p>
                                        <p className="text-xs text-muted-foreground">Born {child.birthdate}</p>
                                    </div>
                                    <button onClick={() => handleUnlink(child.id, child.name)} className="text-xs text-destructive hover:underline">Unlink</button>
                                </div>
                            ))}
                        </section>
                    </>
                )}
                {!isParent && (
                    <section className="space-y-3">
                        <h3 className="text-base font-medium">Your parents</h3>
                        {parents?.length === 0 && (
                            <p className="text-sm text-muted-foreground">No parents linked to your account.</p>
                        )}
                        {parents?.map(parent => (
                            <div key={parent.id} className="rounded-xl border p-4">
                                <p className="font-medium">{parent.name}</p>
                                <p className="text-sm text-muted-foreground">@{parent.username} · {parent.email}</p>
                            </div>
                        ))}
                    </section>
                )}
            </div>
        </>
    );
}
Family.layout = (page: React.ReactNode) => (
    <AppLayout breadcrumbs={breadcrumbs}>{page}</AppLayout>
);