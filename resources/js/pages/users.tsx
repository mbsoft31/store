import AppLayout from '@/layouts/app-layout';
import { Tenant, User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { MoreVertical, Edit, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

function CreateUserModal({ open, onOpenChange, form, errors, onChange, onSave }: any) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create User</DialogTitle>
                </DialogHeader>
                <div className="mb-4">
                    <label className="block mb-1">Name</label>
                    <Input value={form.name} onChange={e => onChange({ ...form, name: e.target.value })} />
                    {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                </div>
                <div className="mb-4">
                    <label className="block mb-1">Email</label>
                    <Input value={form.email} onChange={e => onChange({ ...form, email: e.target.value })} />
                    {errors.email && <p className="text-sm text-red-500 mt-1">{errors.email}</p>}
                </div>
                <div className="mb-4">
                    <label className="block mb-1">Role</label>
                    <Input value={form.role} onChange={e => onChange({ ...form, role: e.target.value })} />
                    {errors.role && <p className="text-sm text-red-500 mt-1">{errors.role}</p>}
                </div>
                <div className="mb-4">
                    <label className="block mb-1">Password</label>
                    <Input type="password" value={form.password} onChange={e => onChange({ ...form, password: e.target.value })} />
                    {errors.password && <p className="text-sm text-red-500 mt-1">{errors.password}</p>}
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
                    <Button onClick={onSave}>Create</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function EditUserModal({ open, onOpenChange, name, email, role, onNameChange, onEmailChange, onRoleChange, onSave }: any) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit User</DialogTitle>
                </DialogHeader>
                <div className="mb-4">
                    <label className="block mb-1">Name</label>
                    <Input value={name} onChange={e => onNameChange(e.target.value)} />
                </div>
                <div className="mb-4">
                    <label className="block mb-1">Email</label>
                    <Input value={email} onChange={e => onEmailChange(e.target.value)} />
                </div>
                <div className="mb-4">
                    <label className="block mb-1">Role</label>
                    <Input value={role} onChange={e => onRoleChange(e.target.value)} />
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
                    <Button onClick={onSave}>Save</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function UserTable({ users, tenant, roleColors, onEdit, onDelete }: any) {
    return (
        <Table>
            <TableCaption>A list of users in the tenant.</TableCaption>
            <TableHeader>
                <TableRow>
                    <TableHead>#</TableHead>
                    <TableHead>Avatar</TableHead>
                    <TableHead>Tenant Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {users.length === 0 ? (
                    <TableRow>
                        <TableCell colSpan={6} className="text-center">No users found.</TableCell>
                    </TableRow>
                ) : (
                    users.map((user: User, idx: number) => (
                        <TableRow key={user.id} className={"hover:bg-muted/50"}>
                            <TableCell>{idx + 1}</TableCell>
                            <TableCell>
                                <Avatar className="h-8 w-8">
                                    <AvatarImage src={user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || user.email)}&background=random`} alt={user.name || user.email} />
                                    <AvatarFallback>{user.name ? tenant.name[0] : user.email[0]}</AvatarFallback>
                                </Avatar>
                            </TableCell>
                            <TableCell>{user.name || tenant.name || <span className="italic text-gray-400">No name</span>}</TableCell>
                            <TableCell>{user.email}</TableCell>
                            <TableCell>
                                <Badge className={roleColors[user.role?.toLowerCase()] || roleColors.default}>
                                    {user.role}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div className="flex gap-2">
                                    <Button size="icon" variant="ghost" title="Edit user" onClick={() => onEdit(user)}>
                                        <Edit className="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" title="Delete user" onClick={() => onDelete(user)}>
                                        <Trash2 className="w-4 h-4 text-red-500" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    ))
                )}
            </TableBody>
        </Table>
    );
}

export default function TenantUsers({ users, tenant }: { users: any[]; tenant: any }) {
    const { auth } = usePage<SharedData>().props;
    const [search, setSearch] = useState('');
    const [editUser, setEditUser] = useState<User | null>(null);
    const [editName, setEditName] = useState('');
    const [editEmail, setEditEmail] = useState('');
    const [editRole, setEditRole] = useState('');
    const [showEditModal, setShowEditModal] = useState(false);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [createForm, setCreateForm] = useState({ name: '', email: '', role: '', password: '' });
    const [createErrors, setCreateErrors] = useState<{ [key: string]: string }>({});

    if (!tenant) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Tenant Users" />
                <div className="flex h-full flex-1 items-center justify-center">
                    <p className="text-lg">No tenant found. Please contact your administrator.</p>
                </div>
            </AppLayout>
        );
    }

    // Filter users by email or name
    const filteredUsers = users.filter((user: User) =>
        (typeof user.email === 'string' && user.email.toLowerCase().includes(search.toLowerCase())) ||
        (typeof user.name === 'string' && user.name.toLowerCase().includes(search.toLowerCase())) ||
        (typeof tenant.name === 'string' && tenant.name.toLowerCase().includes(search.toLowerCase()))
    );

    const roleColors: Record<string, string> = {
        owner: 'bg-blue-100 text-blue-800',
        manager: 'bg-green-100 text-green-800',
        cashier: 'bg-yellow-100 text-yellow-800',
        default: 'bg-gray-100 text-gray-800',
    };

    const handleEdit = (user: User) => {
        setEditUser(user);
        setEditName(String(user.field ?? ''));
        setEditEmail(user.email || '');
        setEditRole(user.role || '');
        setShowEditModal(true);
    };

    const handleEditSave = () => {
        if (!editUser) return;
        router.put(`/users/${editUser.id}`, { name: editName, email: editEmail, role: editRole }, {
            onSuccess: () => setShowEditModal(false),
        });
    };

    const handleDelete = (user: User) => {
        if (window.confirm(`Are you sure you want to delete user ${user.email}?`)) {
            router.delete(`/users/${user.id}`);
        }
    };

    const handleCreate = () => {
        setCreateForm({ name: '', email: '', role: '', password: '' });
        setCreateErrors({});
        setShowCreateModal(true);
    };

    const handleCreateSave = () => {
        router.post('/users', createForm, {
            onSuccess: () => setShowCreateModal(false),
            onError: (errors) => setCreateErrors(errors),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenant Users" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="flex items-center justify-between mb-2">
                    <h1 className="text-2xl font-semibold">Tenant Users</h1>
                    <div className="flex gap-2">
                        <Input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Search by name or email..."
                            className="w-64"
                        />
                        <Button onClick={handleCreate}>Create User</Button>
                    </div>
                </div>
                <UserTable users={filteredUsers} tenant={tenant} roleColors={roleColors} onEdit={handleEdit} onDelete={handleDelete} />
            </div>
            <EditUserModal 
                open={showEditModal} 
                onOpenChange={setShowEditModal} 
                name={editName} 
                email={editEmail}
                role={editRole} 
                onNameChange={setEditName} 
                onEmailChange={setEditEmail}
                onRoleChange={setEditRole} 
                onSave={handleEditSave} 
            />
            <CreateUserModal open={showCreateModal} onOpenChange={setShowCreateModal} form={createForm} errors={createErrors} onChange={setCreateForm} onSave={handleCreateSave} />
        </AppLayout>
    );
}
