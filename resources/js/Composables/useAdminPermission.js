/**
 * useAdminPermission.js
 * Centralizēts admin atļauju pārbaudītājs.
 *
 * Izmantošana jebkurā Admin *.vue:
 *
 *   import { useAdminPermission } from '@/Composables/useAdminPermission.js';
 *   const { can, showUnauthorized, requiredPermission, openUnauthorized, closeUnauthorized,
 *           actionBtnClass, actionBtnStyle, noPermTitle } = useAdminPermission();
 *
 * Priekšnoteikums: HandleInertiaRequests::share() nodod
 *   auth.user.administrator.permissions (masīvs) un
 *   auth.user.is_super_admin (boolean).
 */

import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAdminPermission() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user);

    // is_super_admin ir aksesuārs uz User modeli - nāk tieši no user objekta
    const isSuperAdmin = computed(() => user.value?.is_super_admin === true);

    // Permissions masīvs no administrator relācijas
    // HandleInertiaRequests nodod: auth.user.administrator.permissions
    const permissions = computed(() => {
        if (isSuperAdmin.value) return null; // null = visas atļaujas
        return user.value?.administrator?.permissions ?? [];
    });

    /**
     * Pārbauda vai admins ir konkrēta atļauja.
     * Super admin vienmēr atgriež true.
     */
    const can = (permission) => {
        if (isSuperAdmin.value) return true;
        if (!permissions.value) return true;
        return Array.isArray(permissions.value) && permissions.value.includes(permission);
    };

    /** Vai admins ir KĀDA no norādītajām atļaujām */
    const canAny = (...perms) => perms.some(p => can(p));

    /** Vai admins ir VISAS norādītās atļaujas */
    const canAll = (...perms) => perms.every(p => can(p));

    // ── Unauthorized modal stāvoklis ──────────────────────────────────
    const showUnauthorized = ref(false);
    const requiredPermission = ref(null);

    const openUnauthorized = (permission = null) => {
        requiredPermission.value = permission;
        showUnauthorized.value = true;
    };

    const closeUnauthorized = () => {
        showUnauthorized.value = false;
        requiredPermission.value = null;
    };

    // ── CSS helperi pelēcinātajām pogām ──────────────────────────────
    const actionBtnClass = (hasPermission) =>
        hasPermission ? '' : 'btn-no-permission';

    const actionBtnStyle = (hasPermission) =>
        hasPermission
            ? {}
            : { cursor: 'not-allowed', opacity: '0.45', filter: 'grayscale(0.4)' };

    // ── Tooltip teksts ────────────────────────────────────────────────
    const locale = computed(() =>
        page.props.locale
        ?? (typeof localStorage !== 'undefined' ? localStorage.getItem('lang') : null)
        ?? 'lv'
    );

    const noPermTitle = computed(() =>
        locale.value === 'lv'
            ? 'Jums nav atļaujas veikt šo darbību'
            : 'You do not have permission to perform this action'
    );

    return {
        can,
        canAny,
        canAll,
        isSuperAdmin,
        showUnauthorized,
        requiredPermission,
        openUnauthorized,
        closeUnauthorized,
        actionBtnClass,
        actionBtnStyle,
        noPermTitle,
    };
}
