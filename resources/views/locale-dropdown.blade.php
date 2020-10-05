@if($locale)
  <dropdown-trigger class="h-9 flex items-center">
      <span class="text-90">
          {{ $locale->label }}
      </span>
  </dropdown-trigger>

  @if($locales->isNotEmpty())
      <dropdown-menu slot="menu" width="200" direction="rtl">
          <ul class="list-reset">
              @foreach($locales as $locale)
                  <li>
                      <a href="{{ route('nova-translation.change-locale', ['locale' => $locale->iso]) }}" class="block no-underline text-90 hover:bg-30 p-3">
                          {{ $locale->label }}
                      </a>
                  </li>
              @endforeach
          </ul>
      </dropdown-menu>
  @endif
@endif
