import { computed } from 'vue';
import { useReorder } from '@/composables/useReorder';
import { route } from '@/composables/useRoute';
import type { Skill, SkillIndexProps, UseSkillListReturn } from '@/types';

/**
 * The admin skill list: every skill of every category (from the page props)
 * as one reorderable list, plus the category names and select options.
 */
export function useSkillList(props: SkillIndexProps): UseSkillListReturn {
    const skills = computed(() =>
        props.categories.flatMap((category) => category.skills),
    );

    const {
        items: skillList,
        move,
        saving,
    } = useReorder<Skill>(() => skills.value, route('skillReorder'));

    /** Category name by category ID, for the list's category column. */
    const categoryNames = computed<Record<number, string>>(() =>
        Object.fromEntries(
            props.categories.map((category) => [category.id, category.name]),
        ),
    );

    /** Category choices for the skill form's select. */
    const categoryOptions = computed(() =>
        props.categories.map((category) => ({
            value: category.id,
            label: category.name,
        })),
    );

    return { skillList, move, saving, categoryNames, categoryOptions };
}
