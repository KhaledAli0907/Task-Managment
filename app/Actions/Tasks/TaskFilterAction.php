<?php

namespace App\Actions\Tasks;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskFilterAction
{
    private Builder $query;
    public function __construct(protected Request $request)
    {
    }


    public function handle(Builder $query): Builder
    {
        $this->query = $query;

        return $this
            ->filterByUserRole()
            ->filterByStatus()
            ->filterByAssignee()
            ->filterByDueDateRange()
            ->getQuery();
    }

    protected function filterByUserRole(): static
    {
        $user = auth()->user();

        if ($user->isUser()) {
            $this->query->where('assignee_id', $user->id);
        }

        return $this;
    }


    /**
     * Filter by task status.
     */
    protected function filterByStatus(): static
    {
        if ($status = $this->request->query('status')) {
            $this->query->where('status', $status);
        }

        return $this;
    }

    /**
     * Filter by assigned user.
     */
    protected function filterByAssignee(): static
    {
        if ($assigneeId = $this->request->query('assignee_id')) {
            $this->query->where('assignee_id', $assigneeId);
        }

        return $this;
    }

    /**
     * Filter by due date range (?from=2025-10-01&to=2025-10-15).
     */
    protected function filterByDueDateRange(): static
    {
        $from = $this->request->query('from');
        $to = $this->request->query('to');

        if ($from && $to) {
            $this->query->whereBetween('due_date', [$from, $to]);
        } elseif ($from) {
            $this->query->where('due_date', '>=', $from);
        } elseif ($to) {
            $this->query->where('due_date', '<=', $to);
        }

        return $this;
    }

    /**
     * Return the modified query.
     */
    protected function getQuery(): Builder
    {
        return $this->query;
    }
}
