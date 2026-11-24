import { useReorder } from '@/composables/useReorder';
import { route } from '@/composables/useRoute';
import type {
    Education,
    EducationIndexProps,
    UseEducationListReturn,
} from '@/types';

/**
 * The admin education list: the entries from the page props in their
 * display order, and moving one up or down.
 */
export function useEducationList(
    props: EducationIndexProps,
): UseEducationListReturn {
    const {
        items: educationList,
        move,
        saving,
    } = useReorder<Education>(
        () => props.educations,
        route('educationReorder'),
    );

    return { educationList, move, saving };
}
