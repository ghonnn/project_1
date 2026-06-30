@props([
    'label' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'maxlength' => null,
    'options' => [],
])

<div {{ $attributes->only('class')->class(['space-y-1']) }}>
    @if ($label)
        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    @endif

    <x-filament::input.wrapper>
        @if ($type === 'select')
            <x-filament::input.select>
                @foreach ($options as $option)
                    <option>{{ $option }}</option>
                @endforeach
            </x-filament::input.select>
        @else
            <x-filament::input
                :type="$type"
                :placeholder="$placeholder"
                :value="$value"
                :maxlength="$maxlength"
            />
        @endif
    </x-filament::input.wrapper>
</div>
