<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Models\EditablePages\EditablePage;
use App\Models\EditablePages\EditablePageLayout;
use App\Models\LinkedComponents\LinkedComponentGroup;
use App\Models\SupportedEditablePage;
use App\Models\Themes\Theme;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditorService
{
    private ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * @param Theme $theme
     * @throws QpickHttpException
     * 솔루션의 지원 가능한 에디터 페이지를 에디터 지원 페이지로 등록
     */
    public function createEditablePageForTheme(Theme $theme)
    {
        // 기본 연동 컴포넌트 그룹 생성 (header, footer)
        $linkedComponentGroups = Arr::pluck($this->createLinkedComponentGroup(2), 'id');

        // 지원 가능한 에디터 지원페이지를 해당 테마 하위 에디터 지원페이지로 등록
        $this->getSupportedEditablePageList($theme->getAttribute('solution_id'))->each(
        /**
         * @throws QpickHttpException
         */
            function ($item) use ($theme, $linkedComponentGroups) {
                // 에디터 지원 페이지 추가
                $editablePage = $this->createEditablePage($theme, $item->getAttribute('id'), $item->getAttribute('name'));

                // 에디터 지원 페이지별 레이아웃 등록
                $this->createEditablePageLayout($theme, $editablePage->getAttribute('id'),
                    [
                        'header_component_group_id' => $linkedComponentGroups[0],
                        'footer_component_group_id' => $linkedComponentGroups[1],
                    ]
                );
            }
        );
    }

    /**
     * @throws QpickHttpException
     * 에디터 지원페이지 레리아웃 생성
     */
    public function createEditablePageLayout(Theme $theme, int $editablePageId, array $groups)
    {
        // 작성자 확인
        if (!$this->themeService->usableAuthor($theme)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        if (!isset($groups['header_component_group_id']) || !isset($groups['footer_component_group_id'])) {
            throw new QpickHttpException(422, 'common.bad_request');
        }

        if (!isset($groups['content_component_group_id'])) {
            $groups['content_component_group_id'] = $this->createLinkedComponentGroup()->first()->getattribute('id');
        }

        return EditablePageLayout::create(
            array_merge(
                $groups,
                ['editable_page_id' => $editablePageId]
            )
        )->refresh();
    }

    /**
     * @throws QpickHttpException
     * 에디터 지원 페이지 생성
     */
    public function createEditablePage(Theme $theme, int $supportedEditablePageId, string $name)
    {
        if ($this->checkCreatableEditablePage($theme, $supportedEditablePageId)) {
            return EditablePage::create(
                [
                    'supported_editable_page_id' => $supportedEditablePageId,
                    'name' => $name,
                    'theme_id' => $theme->getAttribute('id'),
                ]
            )->refresh();
        }
    }

    /**
     * @throws QpickHttpException
     * 에디터 지원 페이지 생성시 체크
     */
    protected function checkCreatableEditablePage(Theme $theme, int $supportedEditablePageId): bool
    {
        // check policy
        if (!$this->themeService->usableAuthor($theme)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // exists supported page
        if (EditablePage::where(['theme_id' => $theme->getAttribute('id'), 'supported_editable_page_id' => $supportedEditablePageId])->exists()) {
            throw new QpickHttpException(422, 'supported_editable_page.disable.already_exists');
        }

        // check solution
        $supportedEditablePage = SupportedEditablePage::find($supportedEditablePageId);
        if ($theme->getAttribute('solution_id') != $supportedEditablePage->getAttribute('solution_id')) {
            throw new QpickHttpException(422, 'supported_editable_page.disable.add_to_selected_theme');
        }

        return true;
    }

    /**
     * 연동 컴포넌트 그룹 생성
     */
    protected function createLinkedComponentGroup(int $count = 1): Collection
    {
        return LinkedComponentGroup::factory()->count($count)->create();
    }

    /**
     *  지원 가능한 에디터 페이지 목록 가져오기
     */
    protected function getSupportedEditablePageList($solution)
    {
        return SupportedEditablePage::where('solution_id', $solution)->get();
    }

}
