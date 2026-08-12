import React from 'react';
import { ThemeProvider } from '../../src/design-system/theme/ThemeProvider';

export interface RenderedNode {
  type: any;
  props: Record<string, any>;
  children: RenderedNode[];
  text: string;
  parent?: RenderedNode;
}

export interface RenderResult {
  tree: RenderedNode;
  getByText: (text: string) => RenderedNode | null;
  getByLabel: (label: string) => RenderedNode | null;
  getByRole: (role: string, label?: string) => RenderedNode | null;
  findAll: (predicate: (node: RenderedNode) => boolean) => RenderedNode[];
  press: (node: RenderedNode) => void;
  debug: () => string;
}

export interface RenderOptions {
  language?: 'fr' | 'ar';
  wrapper?: React.ComponentType<{ children: React.ReactNode }>;
}

export function toRenderedTree(node: any, parent?: RenderedNode): RenderedNode | null {
  if (node === null || node === undefined || typeof node === 'boolean') {
    return null;
  }
  if (typeof node === 'string' || typeof node === 'number') {
    const textNode: RenderedNode = {
      type: 'TEXT_NODE',
      props: {},
      children: [],
      text: String(node),
      parent,
    };
    return textNode;
  }

  if (Array.isArray(node)) {
    const resultNode: RenderedNode = {
      type: 'FRAGMENT',
      props: {},
      children: [],
      text: '',
      parent,
    };
    const children = node.map((n) => toRenderedTree(n, resultNode)).filter((n): n is RenderedNode => n !== null);
    resultNode.children = children;
    resultNode.text = children.map((c) => c.text).join(' ');
    return resultNode;
  }

  if (typeof node === 'object' && node.type) {
    let Component = node.type;
    let props = node.props || {};

    const resultNode: RenderedNode = {
      type: typeof Component === 'string' ? Component : Component.displayName || Component.name || 'Component',
      props,
      children: [],
      text: '',
      parent,
    };

    let renderedContent: any = props.children;

    if (typeof Component === 'function') {
      try {
        renderedContent = Component(props);
      } catch (err) {
        if (Component.prototype && Component.prototype.render) {
          const instance = new Component(props);
          renderedContent = instance.render();
        }
      }
    }

    const rawChildren = Array.isArray(renderedContent) ? renderedContent : [renderedContent];
    const children = rawChildren.map((n) => toRenderedTree(n, resultNode)).filter((n): n is RenderedNode => n !== null);
    const directText = typeof renderedContent === 'string' || typeof renderedContent === 'number' ? String(renderedContent) : '';
    const textContent = (directText + ' ' + children.map((c) => c.text).join(' ')).trim();

    resultNode.children = children;
    resultNode.text = textContent;
    return resultNode;
  }

  return null;
}

export function renderWithMayushProviders(
  element: React.ReactElement,
  options: RenderOptions = {}
): RenderResult {
  const language = options.language || 'fr';
  const WrappedApp = () => (
    <ThemeProvider initialLanguage={language}>
      {element}
    </ThemeProvider>
  );

  const rawTree = toRenderedTree(<WrappedApp />);
  const tree = rawTree || { type: 'EMPTY', props: {}, children: [], text: '' };

  const findAll = (predicate: (node: RenderedNode) => boolean): RenderedNode[] => {
    const results: RenderedNode[] = [];
    const walk = (curr: RenderedNode) => {
      if (!curr) return;
      if (predicate(curr)) {
        results.push(curr);
      }
      if (curr.children && curr.children.length) {
        curr.children.forEach(walk);
      }
    };
    walk(tree);
    return results;
  };

  const getByText = (text: string): RenderedNode | null => {
    const nodes = findAll((n) => n.text.includes(text) || (typeof n.props.children === 'string' && n.props.children.includes(text)));
    if (!nodes.length) return null;
    return nodes[nodes.length - 1];
  };

  const getByLabel = (label: string): RenderedNode | null => {
    const nodes = findAll((n) => n.props.accessibilityLabel === label || n.props['aria-label'] === label);
    return nodes.length ? nodes[0] : null;
  };

  const getByRole = (role: string, label?: string): RenderedNode | null => {
    const nodes = findAll((n) => {
      const matchRole = n.props.accessibilityRole === role || n.props.role === role;
      if (!matchRole) return false;
      if (!label) return true;
      return n.props.accessibilityLabel === label || n.text.includes(label);
    });
    return nodes.length ? nodes[0] : null;
  };

  const press = (node: RenderedNode) => {
    if (!node) throw new Error('Cannot press a null or undefined node');
    let curr: RenderedNode | undefined = node;
    while (curr) {
      if (typeof curr.props.onPress === 'function') {
        curr.props.onPress();
        return;
      }
      if (typeof curr.props.onClick === 'function') {
        curr.props.onClick();
        return;
      }
      curr = curr.parent;
    }
    throw new Error(`Node ${node.type} and its parents do not have an onPress or onClick handler`);
  };

  const debug = () => JSON.stringify(tree, null, 2);

  return {
    tree,
    getByText,
    getByLabel,
    getByRole,
    findAll,
    press,
    debug,
  };
}
