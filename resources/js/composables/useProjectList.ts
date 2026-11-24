import { useDebouncedSearch } from '@/composables/useDebouncedSearch';
import { useReorder } from '@/composables/useReorder';
import { route } from '@/composables/useRoute';
import type { Project, ProjectIndexProps, UseProjectListReturn } from '@/types';

/**
 * The admin project list: the projects from the page props in their display
 * order, moving one up or down, and the search box. A search reloads only
 * the `projects` and `filters` props.
 */
export function useProjectList(props: ProjectIndexProps): UseProjectListReturn {
    const {
        items: projectList,
        move,
        saving,
    } = useReorder<Project>(() => props.projects, route('projectReorder'));

    const { search } = useDebouncedSearch(
        route('projectIndex'),
        props.filters.search,
        ['projects', 'filters'],
    );

    return { projectList, move, saving, search };
}
