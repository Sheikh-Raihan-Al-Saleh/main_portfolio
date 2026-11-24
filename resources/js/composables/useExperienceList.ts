import { useReorder } from '@/composables/useReorder';
import { route } from '@/composables/useRoute';
import type {
    Experience,
    ExperienceIndexProps,
    UseExperienceListReturn,
} from '@/types';

/**
 * The admin experience list: the roles from the page props in their display
 * order, and moving one up or down.
 */
export function useExperienceList(
    props: ExperienceIndexProps,
): UseExperienceListReturn {
    const {
        items: experienceList,
        move,
        saving,
    } = useReorder<Experience>(
        () => props.experiences,
        route('experienceReorder'),
    );

    return { experienceList, move, saving };
}
