import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { SharedData, TenantSettings, type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tenant settings',
        href: '/settings/tenant',
    },
];

export default function Tenant() {
    const { auth } = usePage<SharedData>().props;

    const tenant = auth.user.tenant;
    const { data, setData, errors, put, processing, recentlySuccessful } = useForm<{
        name: string;
        settings: TenantSettings;
    }>({
        name: tenant?.name ?? '',
        settings: {
            logo: tenant?.settings?.logo ?? '',
            color: tenant?.settings?.color ?? '',
            currency: tenant?.settings?.currency ?? '',
            timezone: tenant?.settings?.timezone ?? '',
            locale: tenant?.settings?.locale ?? '',
        },
    });

    const updateTenant: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('tenant.settings.update'), {
            preserveScroll: true,
            onSuccess: (page) => {
                // Refresh form fields with new values from the response (if available)
                const tenant = (page.props?.tenant ?? {}) as {
                    name?: string;
                    settings?: {
                        logo?: string;
                        color?: string;
                        currency?: string;
                        timezone?: string;
                        locale?: string;
                    };
                };
                if (tenant) {
                    setData({
                        name: tenant.name ?? '',
                        settings: {
                            logo: tenant.settings?.logo ?? '',
                            color: tenant.settings?.color ?? '',
                            currency: tenant.settings?.currency ?? '',
                            timezone: tenant.settings?.timezone ?? '',
                            locale: tenant.settings?.locale ?? '',
                        },
                    });
                }
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenant settings" />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Update tenant settings" description="Update your store's information and preferences." />
                    <form onSubmit={updateTenant} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">Name</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full"
                                placeholder="Tenant name"
                            />
                            <InputError message={errors.name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="logo">Logo URL</Label>
                            <Input
                                id="logo"
                                value={data.settings.logo}
                                onChange={(e) => setData('settings', { ...data.settings, logo: e.target.value })}
                                className="mt-1 block w-full"
                                placeholder="Logo URL"
                            />
                            {data.settings.logo && (
                                <div className="flex items-center mt-2">
                                    <img
                                        src={data.settings.logo}
                                        alt="Tenant Logo"
                                        className="w-12 h-12 rounded-full border object-cover"
                                    />
                                </div>
                            )}
                            <InputError message={errors['settings.logo']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="color">Color</Label>
                            <Input
                                id="color"
                                type="color"
                                value={data.settings.color}
                                onChange={(e) => setData('settings', { ...data.settings, color: e.target.value })}
                                className="mt-1 block w-full"
                                placeholder="#000000"
                            />
                            <InputError message={errors['settings.color']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="currency">Currency</Label>
                            <Input
                                id="currency"
                                value={data.settings.currency}
                                onChange={(e) => setData('settings', { ...data.settings, currency: e.target.value })}
                                className="mt-1 block w-full"
                                placeholder="USD"
                            />
                            <InputError message={errors['settings.currency']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="timezone">Timezone</Label>
                            <Input
                                id="timezone"
                                value={data.settings.timezone}
                                onChange={(e) => setData('settings', { ...data.settings, timezone: e.target.value })}
                                className="mt-1 block w-full"
                                placeholder="UTC"
                            />
                            <InputError message={errors['settings.timezone']} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="locale">Locale</Label>
                            <Input
                                id="locale"
                                value={data.settings.locale}
                                onChange={(e) => setData('settings', { ...data.settings, locale: e.target.value })}
                                className="mt-1 block w-full"
                                placeholder="en"
                            />
                            <InputError message={errors['settings.locale']} />
                        </div>
                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Save settings</Button>
                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
