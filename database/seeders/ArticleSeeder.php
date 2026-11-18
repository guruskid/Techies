<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $members = User::where('role', 'member')
            ->where('email', 'like', 'member%@example.com')
            ->orderBy('id')
            ->take(8)
            ->get();

        if ($members->isEmpty()) {
            return;
        }

        $reviewerId = User::role('super_admin')->value('id') ?? $members->first()->id;

        foreach ($this->publishedArticles() as $index => $seed) {
            Article::firstOrCreate(['slug' => Str::slug($seed['title'])], [
                'user_id' => $members[$index % $members->count()]->id,
                'title' => $seed['title'],
                'topic' => $seed['topic'],
                'excerpt' => $seed['excerpt'],
                'body' => $seed['body'],
                'cover_image' => 'https://picsum.photos/seed/mwtt-article-'.$index.'/1200/630',
                'status' => Article::STATUS_APPROVED,
                'seo_title' => $seed['title'],
                'seo_description' => Str::limit($seed['excerpt'], 155),
                'seo_keywords' => $seed['keywords'],
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now()->subDays($index + 2),
                'submitted_at' => now()->subDays($index + 3),
                'published_at' => now()->subDays($index + 1),
            ]);
        }

        foreach ($this->pendingArticles() as $index => $seed) {
            Article::firstOrCreate(['slug' => Str::slug($seed['title'])], [
                'user_id' => $members[($index + 6) % $members->count()]->id,
                'title' => $seed['title'],
                'topic' => $seed['topic'],
                'excerpt' => $seed['excerpt'],
                'body' => str_repeat($seed['excerpt'].' ', 4)."\n\n".str_repeat('Share practical examples, explain the trade-offs and give readers a clear next step. ', 8),
                'cover_image' => 'https://picsum.photos/seed/mwtt-pending-'.$index.'/1200/630',
                'status' => Article::STATUS_PENDING,
                'seo_title' => $seed['title'],
                'seo_description' => Str::limit($seed['excerpt'], 155),
                'seo_keywords' => $seed['keywords'],
                'submitted_at' => now()->subHours($index + 2),
            ]);
        }
    }

    /**
     * @return array<int, array{topic: string, title: string, excerpt: string, keywords: string, body: string}>
     */
    private function publishedArticles(): array
    {
        return [
            [
                'topic' => 'engineering',
                'title' => 'How to structure a Laravel application as it grows',
                'excerpt' => 'A practical walkthrough of organising controllers, services and models so a Laravel codebase stays easy to change.',
                'keywords' => 'laravel application structure, laravel, php',
                'body' => "A small Laravel application can live comfortably in a handful of controllers. As the product grows, that convenience starts to work against you: controllers become long, queries are repeated and testing gets harder.\n\nStart by grouping code by the job it does. Keep HTTP concerns in controllers, move reusable business rules into focused service classes, and let models describe relationships and simple domain behaviour. When a screen needs several queries, a dedicated query class or scope keeps those details out of the controller.\n\nNext, make boundaries visible. Form requests handle validation, policies handle authorisation and events handle side effects that should not block a response. This makes each piece easier to test on its own.\n\nFinally, review the structure regularly. Refactoring early is cheaper than untangling a controller that has become the centre of the application. The goal is not a perfect architecture, but a codebase where the next developer can find the right place to make a change.",
            ],
            [
                'topic' => 'career',
                'title' => 'A practical guide to your first technical internship',
                'excerpt' => 'What to prepare, how to ask for help and how to turn an internship into meaningful experience you can build on.',
                'keywords' => 'technical internship, career growth, mentorship',
                'body' => "Landing a technical internship is only the beginning. The people who get the most from the experience are usually the ones who prepare deliberately and communicate clearly.\n\nBefore you start, learn the tools the team already uses. You do not need to master everything, but knowing how to run the project, find the documentation and ask a useful question saves everyone time. Write down what you have tried before asking for help, and share the error message or the behaviour you expected.\n\nTreat small tasks as opportunities to build trust. Finishing a modest piece of work well, testing it and explaining what changed is more valuable than starting something large and leaving it incomplete.\n\nKeep a short weekly log of what you learned and what you shipped. At the end of the internship, that log becomes evidence for your CV, your portfolio and your next interview. Ask for feedback while the work is still fresh, and stay in touch with the people who supported you.",
            ],
            [
                'topic' => 'product',
                'title' => 'Designing product screens people can actually use',
                'excerpt' => 'Simple habits for turning product requirements into clear screens, sensible flows and interfaces that respect the user.',
                'keywords' => 'product design, user experience, interface design',
                'body' => "Good product design is less about decoration and more about reducing the effort required to complete a task. Before opening a design tool, write down who the screen is for and what they need to accomplish.\n\nUse the smallest number of decisions possible. Group related fields, label actions with verbs, and make the next step obvious. If a screen needs a paragraph of instructions, the layout probably needs another pass.\n\nDesign the empty, loading and error states alongside the happy path. These states are where users spend a surprising amount of time, and they deserve the same care as the main screen.\n\nFinally, test with someone who did not build the feature. Watch where they hesitate instead of asking whether they like the design. Their hesitation tells you what to fix, and a short round of observation is often more useful than a long opinion session.",
            ],
            [
                'topic' => 'data-ai',
                'title' => 'Getting started with data storytelling for beginners',
                'excerpt' => 'How to move from a spreadsheet of numbers to a clear story that helps people make a decision.',
                'keywords' => 'data storytelling, data analysis, visualisation',
                'body' => "A dataset is not a story. The story appears when you connect a finding to a decision someone needs to make.\n\nStart with a question, not a chart. Decide what you are trying to understand and who will act on the answer. Then clean the data, check for missing values and write down the assumptions behind every calculation.\n\nWhen you visualise the result, choose the simplest chart that answers the question. Label axes, use consistent units and avoid adding detail that does not change the conclusion. A single clear comparison usually beats a dashboard full of unrelated numbers.\n\nEnd with a short explanation: what you found, how confident you are and what you recommend. Data storytelling is a communication skill, and the people who do it well make their reasoning easy to follow rather than hiding it behind complexity.",
            ],
            [
                'topic' => 'community',
                'title' => 'What community meetups taught me about collaboration',
                'excerpt' => 'Lessons from showing up, asking questions and helping other people learn in a local technology community.',
                'keywords' => 'community meetups, collaboration, tech community',
                'body' => "The first meetup is always the hardest. You do not know anyone, the talks sound advanced and it is tempting to stay in the corner. But community meetups reward the people who keep showing up.\n\nCollaboration starts with small contributions. Ask a thoughtful question, help someone debug an issue after the talk, or offer to share a short demo next time. These moments build trust faster than a stack of business cards.\n\nCommunities also need people who make space for others. Introduce newcomers, repeat context when a conversation assumes too much and celebrate work that is still in progress. A healthy community is not a stage for a few experts; it is a place where people can practise being useful.\n\nOver time, the relationships you build become a support network for your career. You learn how other teams solve problems, find collaborators for side projects and discover opportunities that never appear on a job board.",
            ],
            [
                'topic' => 'security',
                'title' => 'Security habits every developer should build early',
                'excerpt' => 'Practical security habits for day-to-day development, from handling secrets to reviewing dependencies.',
                'keywords' => 'developer security habits, application security, best practices',
                'body' => "Security is easier to maintain when it is part of the normal development routine rather than a final checklist before launch.\n\nBegin with secrets. Keep credentials out of source control, use environment variables and rotate anything that has been exposed. Give each service the minimum access it needs, and avoid sharing one powerful key across the whole team.\n\nTreat every input as untrusted. Validate on the server, escape output in your templates and use parameterised queries. Authentication and authorisation are separate concerns: knowing who a user is does not tell you what they are allowed to do.\n\nKeep dependencies current and review what you add. A package that saves an hour can introduce months of maintenance if it is abandoned. Finally, make reporting easy. People should know how to raise a security concern without fear, and fixes should be prioritised like any other user-facing bug.",
            ],
        ];
    }

    /**
     * @return array<int, array{topic: string, title: string, excerpt: string, keywords: string}>
     */
    private function pendingArticles(): array
    {
        return [
            [
                'topic' => 'engineering',
                'title' => 'Writing better documentation for open source projects',
                'excerpt' => 'A simple documentation structure that helps new contributors understand, install and use your project.',
                'keywords' => 'open source documentation, technical writing',
            ],
            [
                'topic' => 'policy-governance',
                'title' => 'Understanding technology policy for community builders',
                'excerpt' => 'Why technology policy matters to community organisers and how to follow the conversations that affect your work.',
                'keywords' => 'technology policy, community builders, governance',
            ],
        ];
    }
}
