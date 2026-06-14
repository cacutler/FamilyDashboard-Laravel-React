import { Head, Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, CheckSquare, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
type DashboardEvent = {
    id: number;
    name: string;
    location: string;
    start_date: string;
    start_time: string;
    user: { name: string };
};
type DashboardTodo = {
    id: number;
    title: string;
    type: 'chore' | 'reminder';
    completed: boolean;
    assignedTo: { name: string };
};
type FamilyMember = {
    id: number;
    name: string;
    username: string;
    status: 'parent' | 'child';
};
type DashboardStats = {
    events: number;
    todos: number;
    family: number;
};
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url() }
];
export default function Dashboard({events, todos, familyMembers, stats}: {
    events: DashboardEvent[];
    todos: DashboardTodo[];
    familyMembers: FamilyMember[];
    stats: DashboardStats;
}) {
    return (
        <>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Family Dashboard</h1>
                    <p className="text-muted-foreground mt-1 text-sm">Overview of your family events, to-dos, and members.</p>
                </div>
                <div className="grid gap-4 sm:grid-cols-3">
                    <StatCard
                        icon={CalendarDays}
                        label="Events"
                        value={stats.events}
                        href="/events"
                    />
                    <StatCard
                        icon={CheckSquare}
                        label="Open To-Dos"
                        value={stats.todos}
                        href="/todos"
                    />
                    <StatCard
                        icon={Users}
                        label="Family Members"
                        value={stats.family}
                        href="/family"
                    />
                </div>
                <div className="grid gap-4 lg:grid-cols-3">
                    <OverviewCard
                        title="Upcoming Events"
                        description="Next events on the family calendar"
                        href="/events"
                        emptyMessage="No upcoming events."
                    >
                        {events.map((event) => (
                            <li key={event.id} className="space-y-1">
                                <p className="font-medium">{event.name}</p>
                                <p className="text-muted-foreground text-xs">{event.start_date} at {event.start_time}</p>
                                <p className="text-muted-foreground text-xs">{event.location} · {event.user.name}</p>
                            </li>
                        ))}
                    </OverviewCard>
                    <OverviewCard
                        title="Open To-Dos"
                        description="Tasks still in progress"
                        href="/todos"
                        emptyMessage="All caught up!"
                    >
                        {todos.map((todo) => (
                            <li key={todo.id} className="space-y-1">
                                <p className="font-medium">{todo.title}</p>
                                <p className="text-muted-foreground text-xs capitalize">{todo.type} · Assigned to {todo.assignedTo.name}</p>
                            </li>
                        ))}
                    </OverviewCard>
                    <OverviewCard
                        title="Family Members"
                        description="Everyone in your household"
                        href="/family"
                        emptyMessage="No family members linked yet."
                    >
                        {familyMembers.map((member) => (
                            <li
                                key={member.id}
                                className="flex items-center justify-between gap-2"
                            >
                                <div>
                                    <p className="font-medium">{member.name}</p>
                                    <p className="text-muted-foreground text-xs">@{member.username}</p>
                                </div>
                                <span className="bg-secondary text-secondary-foreground rounded-full px-2 py-0.5 text-xs capitalize">{member.status}</span>
                            </li>
                        ))}
                    </OverviewCard>
                </div>
            </div>
        </>
    );
}
function StatCard({icon: Icon, label, value, href}: {
    icon: React.ComponentType<{ className?: string }>;
    label: string;
    value: number;
    href: string;
}) {
    return (
        <Link href={href} className="group block">
            <Card className="transition-colors group-hover:border-primary/40">
                <CardHeader className="flex flex-row items-center justify-between pb-2">
                    <CardDescription>{label}</CardDescription>
                    <Icon className="text-primary size-5" />
                </CardHeader>
                <CardContent>
                    <p className="text-3xl font-semibold">{value}</p>
                </CardContent>
            </Card>
        </Link>
    );
}
function OverviewCard({title, description, href, emptyMessage, children}: {
    title: string;
    description: string;
    href: string;
    emptyMessage: string;
    children: React.ReactNode;
}) {
    const items = Array.isArray(children) ? children : [children];
    const hasItems = items.some(Boolean);
    return (
        <Card className="flex flex-col">
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>{description}</CardDescription>
            </CardHeader>
            <CardContent className="flex-1">
                {hasItems ? (<ul className="space-y-4">{children}</ul>) : (<p className="text-muted-foreground text-sm">{emptyMessage}</p>)}
            </CardContent>
            <CardFooter>
                <Button variant="ghost" size="sm" asChild className="px-0">
                    <Link href={href}>
                        View all
                        <ArrowRight className="ml-1 size-4"/>
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
Dashboard.layout = (page: React.ReactNode) => (<AppLayout breadcrumbs={breadcrumbs}>{page}</AppLayout>);