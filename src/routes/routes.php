<?php

Route::any(config('github-hook.url'), '\HighSolutions\GitHubHook\Controllers\GitHubHookController@fetch');