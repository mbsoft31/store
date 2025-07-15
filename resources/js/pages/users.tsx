import AppLayout from '@/layouts/app-layout';
import { User, type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
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
import { Edit, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

function CreateUserModal({ open, onOpenChange }: { open: boolean; onOpenChange: (open: boolean) => void }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        role: '',
        password: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/users', {
            onSuccess: () => {
                onOpenChange(false);
                reset();
            },
        });
    };

    const handleClose = () => {
        onOpenChange(false);
        reset();
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create User</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block mb-1">Name</label>
                        <Input 
                            value={data.name} 
                            onChange={e => setData('name', e.target.value)}
                            disabled={processing}
                        />
                        {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                    </div>
                    <div className="mb-4">
                        <label className="block mb-1">Email</label>
                        <Input 
                            type="email"
                            value={data.email} 
                            onChange={e => setData('email', e.target.value)}
                            disabled={processing}
                        />
                        {errors.email && <p className="text-sm text-red-500 mt-1">{errors.email}</p>}
                    </div>
                    <div className="mb-4">
                        <label className="block mb-1">Role</label>
                        <Input 
                            value={data.role} 
                            onChange={e => setData('role', e.target.value)}
                            disabled={processing}
                        />
                        {errors.role && <p className="text-sm text-red-500 mt-1">{errors.role}</p>}
                    </div>
                    <div className="mb-4">
                        <label className="block mb-1">Password</label>
                        <Input 
                            type="password" 
                            value={data.password} 
                            onChange={e => setData('password', e.target.value)}
                            disabled={processing}
                        />
                        {errors.password && <p className="text-sm text-red-500 mt-1">{errors.password}</p>}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={handleClose} disabled={processing}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creating...' : 'Create'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

type FormValue = string | number | boolean | File | null | undefined;

interface UserForm {
  name: string;
  email: string;
  role: string;
  [key: string]: FormValue;   // ← makes it a valid FormDataType
}


function EditUserModal({ 
    open, 
    onOpenChange, 
    user 
}: { 
    open: boolean; 
    onOpenChange: (open: boolean) => void; 
    user: User | null;
}) {
    const { data, setData, put, processing, errors, reset } = useForm<any>({
    name: user?.name ?? '',   // use ?? to drop undefined
    email: user?.email ?? '',
    role: user?.role ?? '',
  });

    // Update form data when user changes
    useEffect(() => {
  if (user) {
    setData({
      name: String(user.name ?? ''),
      email: String(user.email ?? ''),
      role: String(user.role ?? ''),
    });
  }
}, [user]);


    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!user) return;
        
        put(`/users/${user.id}`, {
            onSuccess: () => {
                onOpenChange(false);
                reset();
            },
        });
    };

    const handleClose = () => {
        onOpenChange(false);
        reset();
    };

    if (!user) return null;

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit User</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block mb-1">Name</label>
                        <Input 
                            value={data.name} 
                            onChange={e => setData('name', e.target.value)}
                            disabled={processing}
                        />
                        {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                    </div>
                    <div className="mb-4">
                        <label className="block mb-1">Email</label>
                        <Input 
                            type="email"
                            value={data.email} 
                            onChange={e => setData('email', e.target.value)}
                            disabled={processing}
                        />
                        {errors.email && <p className="text-sm text-red-500 mt-1">{errors.email}</p>}
                    </div>
                    <div className="mb-4">
                        <label className="block mb-1">Role</label>
                        <Input 
                            value={data.role} 
                            onChange={e => setData('role', e.target.value)}
                            disabled={processing}
                        />
                        {errors.role && <p className="text-sm text-red-500 mt-1">{errors.role}</p>}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={handleClose} disabled={processing}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save'}
                        </Button>
                    </DialogFooter>
                </form>
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
                                    <AvatarImage src={user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.email)}&background=random`} alt={user.email} />
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
    const [showEditModal, setShowEditModal] = useState(false);
    const [showCreateModal, setShowCreateModal] = useState(false);

    // Delete form using Inertia
    const { delete: deleteUser, processing: deleting } = useForm();

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
        setShowEditModal(true);
    };

    const handleDelete = (user: User) => {
        if (window.confirm(`Are you sure you want to delete user ${user.email}?`)) {
            deleteUser(`/users/${user.id}`);
        }
    };

    const handleCreate = () => {
        setShowCreateModal(true);
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
                user={editUser}
            />
            <CreateUserModal 
                open={showCreateModal} 
                onOpenChange={setShowCreateModal} 
            />
        </AppLayout>
    );
}

